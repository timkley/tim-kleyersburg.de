# Quest Actions Refactor & API Design

## Goal

Extract quest business logic from Livewire components into reusable Action classes, then build a full-featured REST API that delegates to those same actions. Eliminates code duplication between Livewire components (e.g., `toggleComplete` in both `Show` and `Item`).

## Action Pattern

Following Nuno Maduro's essentials conventions:

- `final readonly` classes
- `handle()` method
- Actions validate their own input via `Validator::make()`
- Located in `modules/Holocron/Quest/Actions/`

### Action Classes

| Action | Input | Returns |
|--------|-------|---------|
| `CreateQuest` | `array{name, quest_id?, date?, daily?, is_note?, description?}` | `Quest` |
| `UpdateQuest` | `Quest, array{name?, description?, date?, daily?, is_note?}` | `Quest` |
| `DeleteQuest` | `Quest` | `void` |
| `ToggleQuestComplete` | `Quest` | `Quest` |
| `MoveQuest` | `Quest, ?int $parentId` | `Quest` |
| `PrintQuest` | `Quest` | `Quest` |
| `ToggleAcceptQuest` | `Quest` | `Quest` |
| `AddQuestAttachment` | `Quest, UploadedFile` | `Quest` |
| `RemoveQuestAttachment` | `Quest, string $path` | `Quest` |
| `CreateNote` | `Quest, array{content, role?}` | `Note` |
| `DeleteNote` | `Note` | `void` |
| `AddQuestLink` | `Quest, array{url, title?}` | `Quest` |
| `DeleteQuestLink` | `Quest, int $pivotId` | `void` |
| `SaveReminder` | `Quest, array{type, remind_at?, recurrence_pattern?}` | `Reminder` |
| `DeleteReminder` | `Reminder` | `void` |
| `SaveRecurrence` | `Quest, array{every_x_days, recurrence_type, ends_at?}` | `QuestRecurrence` |
| `DeleteRecurrence` | `Quest` | `void` |

### Example Action

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;

final readonly class CreateQuest
{
    public function handle(array $data): Quest
    {
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'min:1'],
            'quest_id' => ['nullable', 'integer', 'exists:quests,id'],
            'date' => ['nullable', 'date'],
            'daily' => ['nullable', 'boolean'],
            'is_note' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ])->validate();

        return Quest::create($validated);
    }
}
```

## Livewire Refactor

Components become thin UI wrappers that delegate to actions:

```php
public function toggleComplete(ToggleQuestComplete $action): void
{
    $action->handle($this->quest);
    $this->quest->refresh();
}
```

- Actions injected via Livewire method injection
- UI state (`$questDraft`, `$search`, etc.) stays in components
- `updating()` hook calls `UpdateQuest` instead of direct model updates
- `Item.php` delegates to same actions as `Show.php`
- Traits (`WithNotes`, `WithLinks`, `WithReminders`, `WithRecurrence`) delegate to respective actions
- AI operations (`WithAI` trait) stay in Livewire — tightly coupled to streaming lifecycle

## API Layer

### Authentication

Existing `BearerToken` middleware on the API route group (shared bearer token).

### Routes (`modules/Holocron/Quest/Routes/api.php`)

```
GET     /api/holocron/quests                                → list
POST    /api/holocron/quests                                → create
GET     /api/holocron/quests/{quest}                        → show
PATCH   /api/holocron/quests/{quest}                        → update
DELETE  /api/holocron/quests/{quest}                        → delete
POST    /api/holocron/quests/{quest}/complete                → toggle complete
POST    /api/holocron/quests/{quest}/move                    → reparent
POST    /api/holocron/quests/{quest}/print                   → trigger print
POST    /api/holocron/quests/{quest}/accept                  → toggle accept

POST    /api/holocron/quests/{quest}/attachments              → upload
DELETE  /api/holocron/quests/{quest}/attachments/{path}        → remove

GET     /api/holocron/quests/{quest}/notes                   → list
POST    /api/holocron/quests/{quest}/notes                   → create
DELETE  /api/holocron/quests/{quest}/notes/{note}             → delete

GET     /api/holocron/quests/{quest}/links                   → list
POST    /api/holocron/quests/{quest}/links                   → add
DELETE  /api/holocron/quests/{quest}/links/{pivotId}          → remove

GET     /api/holocron/quests/{quest}/reminders               → list
POST    /api/holocron/quests/{quest}/reminders               → save
DELETE  /api/holocron/quests/{quest}/reminders/{reminder}     → delete

GET     /api/holocron/quests/{quest}/recurrence              → show
POST    /api/holocron/quests/{quest}/recurrence              → save
DELETE  /api/holocron/quests/{quest}/recurrence              → delete
```

### API Resources

- `QuestResource` — with optional includes for children, notes, links, reminders, recurrence
- `NoteResource`
- `ReminderResource`
- `QuestRecurrenceResource`
- `WebpageResource`

### Controllers

- `QuestController` — CRUD + toggle/move/print/accept
- `QuestAttachmentController`
- `QuestNoteController`
- `QuestLinkController`
- `QuestReminderController`
- `QuestRecurrenceController`

## Testing

### Action Tests (`modules/Holocron/Quest/Tests/Actions/`)

Each action gets its own test file covering:
- Validation rules (required fields, invalid data)
- Happy path behavior
- Edge cases

### API Tests (`modules/Holocron/Quest/Tests/Api/`)

Each endpoint tested for:
- Authentication (401 without token)
- Success responses
- Validation errors (422)
- Not found (404)

### Existing Tests

Livewire tests updated to reflect refactored components. No tests removed.

## Execution Order

1. **Phase 1: Actions** — Create action classes + action tests
2. **Phase 2: Livewire refactor** — Wire components to actions, update existing tests
3. **Phase 3: API** — Controllers, resources, routes, API tests
