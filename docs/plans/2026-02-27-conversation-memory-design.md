# Chopper Conversation Memory via Typesense Search

## Context

Chopper has no long-term memory across conversations. Each conversation is isolated. Conversations are already persisted in `agent_conversations` / `agent_conversation_messages` tables via Laravel AI's `RemembersConversations` trait.

## Goal

Give Chopper proactive recall of relevant past conversations using semantic search. Keep context small with a token-budgeted approach.

## Design Decisions

- **Index individual messages** (user + assistant only, skip tool calls/results) for granular semantic search
- **Pre-generate conversation summaries** via a scheduled Artisan command — not on-the-fly
- **Single tool** (`SearchConversationHistory`) with token-budgeted results — no two-stage retrieval
- **Proactive usage** — Chopper searches memory whenever past context could be useful, not only when explicitly asked

## Components

### 1. Migration

Add to `agent_conversations` table:

- `summary` (text, nullable) — LLM-generated conversation summary
- `summary_generated_at` (timestamp, nullable) — tracks when summary was last generated

### 2. Model: AgentConversationMessage

- Uses Scout `Searchable` trait
- `toSearchableArray()` returns: `id`, `conversation_id`, `role`, `content`, `created_at`
- Only indexes messages where `role` is `user` or `assistant`

### 3. Typesense Collection

New entry in `config/scout.php`:

- Fields: `id`, `conversation_id`, `role`, `content`, `created_at`, `embedding`
- Embedding on `content` using `openai/text-embedding-3-small` (same as Quest/Note collections)
- Search params: `query_by: 'content'`, `exclude_fields: 'embedding'`

### 4. Artisan Command: `conversations:summarize`

Finds conversations that need summarization:

- Last message older than 30 minutes (conversation is "idle")
- AND either: no summary yet, OR has messages created after `summary_generated_at`

For each matching conversation:

1. Sends user + assistant message content to a cheap/fast LLM via Laravel AI / OpenRouter
2. Prompt: summarize in 2-3 sentences, focus on topics, decisions, key information
3. Updates `summary` and `summary_generated_at`

Scheduled to run every 15-30 minutes.

### 5. Tool: SearchConversationHistory

**Input:** `query` (string), `limit` (integer, default 10)

**Behavior:**

1. Semantic search via `AgentConversationMessage::search()` with `query_by: 'embedding'`
2. Eager-load parent `AgentConversation` with summary
3. Build response incrementally, tracking token count (~4 chars/token):
   - Per match: role, content, date, conversation ID
   - Per unique conversation: summary (deduplicated)
   - Stop at ~25k tokens
   - Truncate individual messages exceeding ~5k tokens
4. Return formatted text grouped by conversation

### 6. Agent Instructions Update

Add to `ChopperAgent.php` instructions: use `SearchConversationHistory` proactively whenever memory could enrich the response. Search when the user mentions a topic that might have been discussed before, when past context could improve the answer, or when there's continuity with previous interactions. Don't wait for explicit "remember when" — if there's a chance past context is relevant, search for it.
