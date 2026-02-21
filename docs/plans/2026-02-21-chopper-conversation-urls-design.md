# Chopper Conversation URLs Design

## Problem

Chopper chat conversations don't have dedicated URLs. The entire app lives at `/holocron/chopper` and conversation selection is purely Livewire state (`$conversationId` property). On page reload, `$conversationId` resets to `null`, showing the empty state instead of the conversation the user was viewing.

## Solution

Add an optional route parameter so each conversation has its own URL: `/holocron/chopper/{conversationId?}`.

## Approach: Route parameter + `mount()` + `wire:navigate`

### Routing

Change the existing route to accept an optional conversation ID:

```php
Route::livewire('/chopper/{conversationId?}', Chopper::class)->name('chopper');
```

- `/holocron/chopper` = new conversation (empty state)
- `/holocron/chopper/{uuid}` = specific conversation

The named route `holocron.chopper` continues to work for both cases via `route('holocron.chopper')` and `route('holocron.chopper', $id)`.

### Livewire Component Changes

1. **`mount(?string $conversationId = null)`** - Accept the route parameter, validate ownership, load messages if provided
2. **`selectConversation()`** - Replace Livewire state mutation with `$this->redirect()` to the conversation URL
3. **`newConversation()`** - Replace with `$this->redirect()` to `/holocron/chopper`
4. **After `ask()` creates a new conversation** - Redirect to the new conversation URL so the address bar updates
5. **Security**: Verify the conversation belongs to `auth()->id()` in `mount()`, abort 404 if not found or unauthorized

### Blade View Changes

Sidebar conversation buttons become `<a>` tags with `wire:navigate` for SPA-like transitions:

- Conversation items: `<a href="{{ route('holocron.chopper', $conv->id) }}" wire:navigate>`
- New conversation button: `<a href="{{ route('holocron.chopper') }}" wire:navigate>`
- Mobile dropdown items: same pattern

### Tests

- Visiting `/holocron/chopper/{id}` loads that conversation's messages
- Visiting with an invalid or another user's conversation ID returns 404
- Route generation with and without ID works correctly
- Existing tests updated for new route signature
