# AI SDK Chopper Agent Design

## Overview

Replace the current Chopper RAG Q&A interface with a full AI agent built on the Laravel AI SDK (`laravel/ai`). Remove `prism-php/prism` entirely and migrate all 6 Prism call sites to the AI SDK.

## Goals

- Multi-turn conversational agent with persistent conversation history
- Tools for reading and writing quests and notes
- Simple chat UI with conversation sidebar and streaming responses
- Remove Prism dependency, standardize on Laravel AI SDK

## ChopperAgent

**File:** `app/Ai/Agents/ChopperAgent.php`

- Implements `Agent`, `Conversational`, `HasTools`
- Uses `Promptable`, `RemembersConversations` traits
- Provider: OpenRouter, Model: `google/gemini-2.5-flash`
- German-language system prompt (assistant personality based on C1-10P from Star Wars Rebels)
- Conversations persisted via `RemembersConversations` (automatic DB storage)

### Tools

All tools live in `app/Ai/Tools/`.

| Tool | Description | Type |
|------|-------------|------|
| `SearchQuests` | Vector search quests via Scout/Typesense embeddings | Read |
| `SearchNotes` | Vector search quest notes via Scout/Typesense embeddings | Read |
| `ListQuests` | List open quests with filters (today, daily, notes-only) | Read |
| `GetQuest` | Get a specific quest with its children and notes | Read |
| `CreateQuest` | Create a new quest (with optional `is_note` flag) | Write |
| `CompleteQuest` | Mark a quest as completed | Write |
| `AddNoteToQuest` | Add a comment/note to an existing quest | Write |

## Chat UI

**Files:**
- `modules/Holocron/_Shared/Livewire/Chopper.php` (rewritten)
- `modules/Holocron/_Shared/Views/chopper.blade.php` (rewritten)

**Route:** `GET /holocron/chopper` (unchanged)

### Layout

- **Sidebar (left):** List of past conversations + "New conversation" button
- **Chat area (right):** Message thread (Markdown rendered) + input at bottom
- **Mobile:** Sidebar collapses to dropdown/modal
- Uses Flux UI components throughout

### Behavior

- User sends a message → Livewire calls `ChopperAgent::continue($conversationId)->prompt($message)`
- Streaming via `->stream()` piped to Livewire's `$this->stream()`
- New conversations created via `ChopperAgent::forUser($user)->prompt($message)`, returns `conversationId`
- Past conversations loaded from `ai_conversations` table
- Conversation titles auto-generated from first message
- Tool calls are not visually surfaced — agent responds naturally

## Prism Migration

Remove `prism-php/prism` entirely. All call sites migrate to Laravel AI SDK.

### Call Site Mapping

| File | Current | New |
|------|---------|-----|
| `Holocron/_Shared/Livewire/Chopper.php` | Prism RAG Q&A + streaming | ChopperAgent (full agent) |
| `app/Notifications/Chopper.php` | Prism conversation with cache history | Anonymous agent with manual messages |
| `app/Livewire/Articles/Show.php` | Prism one-shot text | Anonymous agent one-shot |
| `Holocron/Quest/Livewire/WithNotes.php` | Prism chat + streaming | Anonymous agent with streaming |
| `Holocron/Quest/Livewire/WithAI.php` | Prism structured output | Anonymous agent with structured output |
| `Holocron/Bookmarks/Jobs/CrawlWebpageInformation.php` | Prism one-shot text | Anonymous agent one-shot |

### Config Changes

- Remove `config/prism.php`
- Add `config/ai.php` with OpenRouter provider
- Reuse existing `OPENROUTER_API_KEY` env var

## Testing

- `modules/Holocron/_Shared/Tests/ChopperTest.php` — new test file for the agent
- Use `ChopperAgent::fake()` for mocking responses
- Test tool invocations and conversation persistence
- Update existing tests that mock Prism to use AI SDK fake utilities

## Dependencies

- Install: `laravel/ai` (requires `prism-php/prism ^0.99.0` — automatic upgrade)
- Remove: direct `prism-php/prism` dependency (becomes a transitive dep of `laravel/ai`)
- Run: `php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"` + `php artisan migrate`
