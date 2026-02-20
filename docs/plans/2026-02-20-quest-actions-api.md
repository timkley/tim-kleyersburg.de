# Quest Actions Refactor & API Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Extract quest business logic from Livewire components into reusable Action classes, then build a full REST API that delegates to those same actions.

**Architecture:** Actions are `final readonly` classes with `handle()` methods (Nuno Maduro's essentials pattern). Each validates its own input via `Validator::make()`. Livewire components and API controllers both delegate to actions. API uses existing `BearerToken` middleware with Eloquent API Resources.

**Tech Stack:** Laravel 12, Livewire 3, Pest 4, Eloquent API Resources

---

## Phase 1: Action Classes + Tests

### Task 1: CreateQuest Action

**Files:**
- Create: `modules/Holocron/Quest/Actions/CreateQuest.php`
- Test: `modules/Holocron/Quest/Tests/Actions/CreateQuestTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\CreateQuest;
use Modules\Holocron\Quest\Models\Quest;

it('creates a quest with required fields', function () {
    $quest = (new CreateQuest)->handle([
        'name' => 'Defeat the dragon',
    ]);

    expect($quest)->toBeInstanceOf(Quest::class)
        ->and($quest->name)->toBe('Defeat the dragon')
        ->and($quest->exists)->toBeTrue();
});

it('creates a quest with all optional fields', function () {
    $parent = Quest::factory()->create();

    $quest = (new CreateQuest)->handle([
        'name' => 'Find the sword',
        'quest_id' => $parent->id,
        'date' => '2026-03-01',
        'daily' => true,
        'is_note' => false,
        'description' => 'Look in the cave',
    ]);

    expect($quest->quest_id)->toBe($parent->id)
        ->and($quest->date->format('Y-m-d'))->toBe('2026-03-01')
        ->and($quest->daily)->toBeTrue()
        ->and($quest->is_note)->toBeFalse()
        ->and($quest->description)->toBe('Look in the cave');
});

it('validates name is required', function () {
    (new CreateQuest)->handle([]);
})->throws(Illuminate\Validation\ValidationException::class);

it('validates quest_id exists', function () {
    (new CreateQuest)->handle([
        'name' => 'Test',
        'quest_id' => 99999,
    ]);
})->throws(Illuminate\Validation\ValidationException::class);
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/CreateQuestTest.php`
Expected: FAIL — class not found

**Step 3: Write the action**

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

**Step 4: Run test to verify it passes**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/CreateQuestTest.php`
Expected: PASS (4 tests)

**Step 5: Commit**

```
git add modules/Holocron/Quest/Actions/CreateQuest.php modules/Holocron/Quest/Tests/Actions/CreateQuestTest.php
git commit -m "feat: add CreateQuest action with tests"
```

---

### Task 2: UpdateQuest Action

**Files:**
- Create: `modules/Holocron/Quest/Actions/UpdateQuest.php`
- Test: `modules/Holocron/Quest/Tests/Actions/UpdateQuestTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\UpdateQuest;
use Modules\Holocron\Quest\Models\Quest;

it('updates a quest name', function () {
    $quest = Quest::factory()->create(['name' => 'Old name']);

    $updated = (new UpdateQuest)->handle($quest, ['name' => 'New name']);

    expect($updated->name)->toBe('New name');
});

it('updates multiple fields at once', function () {
    $quest = Quest::factory()->create();

    $updated = (new UpdateQuest)->handle($quest, [
        'name' => 'Updated',
        'description' => 'New desc',
        'date' => '2026-04-01',
    ]);

    expect($updated->name)->toBe('Updated')
        ->and($updated->description)->toBe('New desc')
        ->and($updated->date->format('Y-m-d'))->toBe('2026-04-01');
});

it('allows partial updates', function () {
    $quest = Quest::factory()->create(['name' => 'Keep this', 'description' => 'Old']);

    $updated = (new UpdateQuest)->handle($quest, ['description' => 'New']);

    expect($updated->name)->toBe('Keep this')
        ->and($updated->description)->toBe('New');
});

it('validates name min length when provided', function () {
    $quest = Quest::factory()->create();

    (new UpdateQuest)->handle($quest, ['name' => '']);
})->throws(Illuminate\Validation\ValidationException::class);
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/UpdateQuestTest.php`

**Step 3: Write the action**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;

final readonly class UpdateQuest
{
    public function handle(Quest $quest, array $data): Quest
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'string', 'min:1'],
            'description' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'daily' => ['sometimes', 'boolean'],
            'is_note' => ['sometimes', 'boolean'],
        ])->validate();

        $quest->update($validated);

        return $quest->refresh();
    }
}
```

**Step 4: Run test to verify it passes**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/UpdateQuestTest.php`

**Step 5: Commit**

```
git add modules/Holocron/Quest/Actions/UpdateQuest.php modules/Holocron/Quest/Tests/Actions/UpdateQuestTest.php
git commit -m "feat: add UpdateQuest action with tests"
```

---

### Task 3: DeleteQuest Action

**Files:**
- Create: `modules/Holocron/Quest/Actions/DeleteQuest.php`
- Test: `modules/Holocron/Quest/Tests/Actions/DeleteQuestTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\DeleteQuest;
use Modules\Holocron\Quest\Models\Quest;

it('deletes a quest', function () {
    $quest = Quest::factory()->create();

    (new DeleteQuest)->handle($quest);

    expect(Quest::find($quest->id))->toBeNull();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/DeleteQuestTest.php`

**Step 3: Write the action**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class DeleteQuest
{
    public function handle(Quest $quest): void
    {
        $quest->delete();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/DeleteQuestTest.php`

```
git add modules/Holocron/Quest/Actions/DeleteQuest.php modules/Holocron/Quest/Tests/Actions/DeleteQuestTest.php
git commit -m "feat: add DeleteQuest action with tests"
```

---

### Task 4: ToggleQuestComplete Action

**Files:**
- Create: `modules/Holocron/Quest/Actions/ToggleQuestComplete.php`
- Test: `modules/Holocron/Quest/Tests/Actions/ToggleQuestCompleteTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\ToggleQuestComplete;
use Modules\Holocron\Quest\Models\Quest;

it('completes an incomplete quest', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    (new ToggleQuestComplete)->handle($quest);

    $quest->refresh();
    expect($quest->completed_at)->not->toBeNull();
});

it('uncompletes a completed quest', function () {
    $quest = Quest::factory()->create(['completed_at' => now()]);

    (new ToggleQuestComplete)->handle($quest);

    $quest->refresh();
    expect($quest->completed_at)->toBeNull();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/ToggleQuestCompleteTest.php`

**Step 3: Write the action**

The existing `Quest::complete()` method handles XP and deferred logic. Reuse it.

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class ToggleQuestComplete
{
    public function handle(Quest $quest): Quest
    {
        if ($quest->isCompleted()) {
            $quest->update(['completed_at' => null]);
        } else {
            $quest->complete();
        }

        return $quest->refresh();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/ToggleQuestCompleteTest.php`

```
git add modules/Holocron/Quest/Actions/ToggleQuestComplete.php modules/Holocron/Quest/Tests/Actions/ToggleQuestCompleteTest.php
git commit -m "feat: add ToggleQuestComplete action with tests"
```

---

### Task 5: MoveQuest Action

**Files:**
- Create: `modules/Holocron/Quest/Actions/MoveQuest.php`
- Test: `modules/Holocron/Quest/Tests/Actions/MoveQuestTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\MoveQuest;
use Modules\Holocron\Quest\Models\Quest;

it('moves a quest to a new parent', function () {
    $quest = Quest::factory()->create();
    $newParent = Quest::factory()->create();

    $moved = (new MoveQuest)->handle($quest, ['quest_id' => $newParent->id]);

    expect($moved->quest_id)->toBe($newParent->id);
});

it('moves a quest to root by setting null', function () {
    $parent = Quest::factory()->create();
    $quest = Quest::factory()->create(['quest_id' => $parent->id]);

    $moved = (new MoveQuest)->handle($quest, ['quest_id' => null]);

    expect($moved->quest_id)->toBeNull();
});

it('validates parent exists', function () {
    $quest = Quest::factory()->create();

    (new MoveQuest)->handle($quest, ['quest_id' => 99999]);
})->throws(Illuminate\Validation\ValidationException::class);
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/MoveQuestTest.php`

**Step 3: Write the action**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;

final readonly class MoveQuest
{
    public function handle(Quest $quest, array $data): Quest
    {
        $validated = Validator::make($data, [
            'quest_id' => ['nullable', 'integer', 'exists:quests,id'],
        ])->validate();

        $quest->update(['quest_id' => $validated['quest_id'] ?? null]);

        return $quest->refresh();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/MoveQuestTest.php`

```
git add modules/Holocron/Quest/Actions/MoveQuest.php modules/Holocron/Quest/Tests/Actions/MoveQuestTest.php
git commit -m "feat: add MoveQuest action with tests"
```

---

### Task 6: PrintQuest Action

**Files:**
- Create: `modules/Holocron/Quest/Actions/PrintQuest.php`
- Test: `modules/Holocron/Quest/Tests/Actions/PrintQuestTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\PrintQuest;
use Modules\Holocron\Quest\Models\Quest;

it('sets should_be_printed to true', function () {
    $quest = Quest::factory()->create(['should_be_printed' => false]);

    $result = (new PrintQuest)->handle($quest);

    expect($result->should_be_printed)->toBeTrue();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/PrintQuestTest.php`

**Step 3: Write the action**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class PrintQuest
{
    public function handle(Quest $quest): Quest
    {
        $quest->update(['should_be_printed' => true]);

        return $quest->refresh();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/PrintQuestTest.php`

```
git add modules/Holocron/Quest/Actions/PrintQuest.php modules/Holocron/Quest/Tests/Actions/PrintQuestTest.php
git commit -m "feat: add PrintQuest action with tests"
```

---

### Task 7: ToggleAcceptQuest Action

**Files:**
- Create: `modules/Holocron/Quest/Actions/ToggleAcceptQuest.php`
- Test: `modules/Holocron/Quest/Tests/Actions/ToggleAcceptQuestTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\ToggleAcceptQuest;
use Modules\Holocron\Quest\Models\Quest;

it('accepts an unaccepted quest by setting date to today', function () {
    $quest = Quest::factory()->create(['date' => null]);

    $result = (new ToggleAcceptQuest)->handle($quest);

    expect($result->date)->not->toBeNull()
        ->and($result->date->isToday())->toBeTrue();
});

it('unaccepts an accepted quest by clearing date', function () {
    $quest = Quest::factory()->create(['date' => now()]);

    $result = (new ToggleAcceptQuest)->handle($quest);

    expect($result->date)->toBeNull();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/ToggleAcceptQuestTest.php`

**Step 3: Write the action**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class ToggleAcceptQuest
{
    public function handle(Quest $quest): Quest
    {
        $quest->update(['date' => $quest->date ? null : now()]);

        return $quest->refresh();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/ToggleAcceptQuestTest.php`

```
git add modules/Holocron/Quest/Actions/ToggleAcceptQuest.php modules/Holocron/Quest/Tests/Actions/ToggleAcceptQuestTest.php
git commit -m "feat: add ToggleAcceptQuest action with tests"
```

---

### Task 8: AddQuestAttachment + RemoveQuestAttachment Actions

**Files:**
- Create: `modules/Holocron/Quest/Actions/AddQuestAttachment.php`
- Create: `modules/Holocron/Quest/Actions/RemoveQuestAttachment.php`
- Test: `modules/Holocron/Quest/Tests/Actions/QuestAttachmentActionsTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Quest\Actions\AddQuestAttachment;
use Modules\Holocron\Quest\Actions\RemoveQuestAttachment;
use Modules\Holocron\Quest\Models\Quest;

it('adds an attachment to a quest', function () {
    Storage::fake('public');

    $quest = Quest::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $result = (new AddQuestAttachment)->handle($quest, $file);

    expect($result->attachments)->toHaveCount(1);
    Storage::disk('public')->assertExists($result->attachments->first());
});

it('appends to existing attachments', function () {
    Storage::fake('public');

    $existingPath = UploadedFile::fake()->image('old.jpg')->store('quests', 'public');
    $quest = Quest::factory()->create(['attachments' => [$existingPath]]);
    $file = UploadedFile::fake()->image('new.jpg');

    $result = (new AddQuestAttachment)->handle($quest, $file);

    expect($result->attachments)->toHaveCount(2);
});

it('removes an attachment from a quest', function () {
    Storage::fake('public');

    $path = UploadedFile::fake()->image('photo.jpg')->store('quests', 'public');
    $quest = Quest::factory()->create(['attachments' => [$path]]);

    (new RemoveQuestAttachment)->handle($quest, $path);

    $quest->refresh();
    expect($quest->attachments)->toHaveCount(0);
    Storage::disk('public')->assertMissing($path);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/QuestAttachmentActionsTest.php`

**Step 3: Write the actions**

`AddQuestAttachment.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Http\UploadedFile;
use Modules\Holocron\Quest\Models\Quest;

final readonly class AddQuestAttachment
{
    public function handle(Quest $quest, UploadedFile $file): Quest
    {
        $storedPath = $file->store('quests', 'public');

        if ($storedPath) {
            $quest->update([
                'attachments' => $quest->attachments->push($storedPath),
            ]);
        }

        return $quest->refresh();
    }
}
```

`RemoveQuestAttachment.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Quest\Models\Quest;

final readonly class RemoveQuestAttachment
{
    public function handle(Quest $quest, string $path): Quest
    {
        Storage::disk('public')->delete($path);

        $quest->update([
            'attachments' => $quest->attachments->filter(fn ($a) => $a !== $path)->values(),
        ]);

        return $quest->refresh();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/QuestAttachmentActionsTest.php`

```
git add modules/Holocron/Quest/Actions/AddQuestAttachment.php modules/Holocron/Quest/Actions/RemoveQuestAttachment.php modules/Holocron/Quest/Tests/Actions/QuestAttachmentActionsTest.php
git commit -m "feat: add quest attachment actions with tests"
```

---

### Task 9: CreateNote + DeleteNote Actions

**Files:**
- Create: `modules/Holocron/Quest/Actions/CreateNote.php`
- Create: `modules/Holocron/Quest/Actions/DeleteNote.php`
- Test: `modules/Holocron/Quest/Tests/Actions/NoteActionsTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\CreateNote;
use Modules\Holocron\Quest\Actions\DeleteNote;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

it('creates a note for a quest', function () {
    $quest = Quest::factory()->create();

    $note = (new CreateNote)->handle($quest, ['content' => 'Remember this']);

    expect($note)->toBeInstanceOf(Note::class)
        ->and($note->quest_id)->toBe($quest->id)
        ->and($note->content)->toBe('Remember this')
        ->and($note->role)->toBe('user');
});

it('creates a note with custom role', function () {
    $quest = Quest::factory()->create();

    $note = (new CreateNote)->handle($quest, [
        'content' => 'AI response',
        'role' => 'assistant',
    ]);

    expect($note->role)->toBe('assistant');
});

it('validates content is required', function () {
    $quest = Quest::factory()->create();

    (new CreateNote)->handle($quest, []);
})->throws(Illuminate\Validation\ValidationException::class);

it('deletes a note', function () {
    $note = Note::factory()->create();

    (new DeleteNote)->handle($note);

    expect(Note::find($note->id))->toBeNull();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/NoteActionsTest.php`

**Step 3: Write the actions**

`CreateNote.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

final readonly class CreateNote
{
    public function handle(Quest $quest, array $data): Note
    {
        $validated = Validator::make($data, [
            'content' => ['required', 'string'],
            'role' => ['sometimes', 'string', 'in:user,assistant'],
        ])->validate();

        return $quest->notes()->create([
            'content' => $validated['content'],
            'role' => $validated['role'] ?? 'user',
        ]);
    }
}
```

`DeleteNote.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Note;

final readonly class DeleteNote
{
    public function handle(Note $note): void
    {
        $note->delete();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/NoteActionsTest.php`

```
git add modules/Holocron/Quest/Actions/CreateNote.php modules/Holocron/Quest/Actions/DeleteNote.php modules/Holocron/Quest/Tests/Actions/NoteActionsTest.php
git commit -m "feat: add note actions with tests"
```

---

### Task 10: AddQuestLink + DeleteQuestLink Actions

**Files:**
- Create: `modules/Holocron/Quest/Actions/AddQuestLink.php`
- Create: `modules/Holocron/Quest/Actions/DeleteQuestLink.php`
- Test: `modules/Holocron/Quest/Tests/Actions/QuestLinkActionsTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Actions\AddQuestLink;
use Modules\Holocron\Quest\Actions\DeleteQuestLink;
use Modules\Holocron\Quest\Models\Quest;

it('adds a link to a quest', function () {
    Bus::fake();

    $quest = Quest::factory()->create();

    $result = (new AddQuestLink)->handle($quest, ['url' => 'https://example.com']);

    expect($quest->webpages()->count())->toBe(1);
    Bus::assertDispatched(CrawlWebpageInformation::class);
});

it('reuses existing webpage when adding duplicate url', function () {
    Bus::fake();

    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create(['url' => 'https://example.com']);

    (new AddQuestLink)->handle($quest, ['url' => 'https://example.com']);

    expect(Webpage::count())->toBe(1)
        ->and($quest->webpages()->first()->id)->toBe($webpage->id);
});

it('validates url is required', function () {
    $quest = Quest::factory()->create();

    (new AddQuestLink)->handle($quest, []);
})->throws(Illuminate\Validation\ValidationException::class);

it('validates url format', function () {
    $quest = Quest::factory()->create();

    (new AddQuestLink)->handle($quest, ['url' => 'not-a-url']);
})->throws(Illuminate\Validation\ValidationException::class);

it('deletes a quest link pivot without deleting webpage', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Test']);

    $pivotId = $quest->webpages()->first()->pivot->id;

    (new DeleteQuestLink)->handle($quest, $pivotId);

    expect($quest->webpages()->count())->toBe(0)
        ->and(Webpage::find($webpage->id))->not->toBeNull();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/QuestLinkActionsTest.php`

**Step 3: Write the actions**

`AddQuestLink.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Models\Quest;

final readonly class AddQuestLink
{
    public function handle(Quest $quest, array $data): Quest
    {
        $validated = Validator::make($data, [
            'url' => ['required', 'url'],
            'title' => ['nullable', 'string'],
        ])->validate();

        $webpage = Webpage::createOrFirst([
            'url' => $validated['url'],
        ]);

        if ($webpage->wasRecentlyCreated) {
            CrawlWebpageInformation::dispatch($webpage);
        }

        $quest->webpages()->attach($webpage, [
            'title' => $validated['title'] ?? null,
        ]);

        return $quest->refresh();
    }
}
```

`DeleteQuestLink.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class DeleteQuestLink
{
    public function handle(Quest $quest, int $pivotId): void
    {
        $quest->webpages()->wherePivot('id', $pivotId)->detach();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/QuestLinkActionsTest.php`

```
git add modules/Holocron/Quest/Actions/AddQuestLink.php modules/Holocron/Quest/Actions/DeleteQuestLink.php modules/Holocron/Quest/Tests/Actions/QuestLinkActionsTest.php
git commit -m "feat: add quest link actions with tests"
```

---

### Task 11: SaveReminder + DeleteReminder Actions

**Files:**
- Create: `modules/Holocron/Quest/Actions/SaveReminder.php`
- Create: `modules/Holocron/Quest/Actions/DeleteReminder.php`
- Test: `modules/Holocron/Quest/Tests/Actions/ReminderActionsTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\DeleteReminder;
use Modules\Holocron\Quest\Actions\SaveReminder;
use Modules\Holocron\Quest\Enums\ReminderType;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

it('creates a one-time reminder', function () {
    $quest = Quest::factory()->create();

    $reminder = (new SaveReminder)->handle($quest, [
        'remind_at' => '2026-03-01 09:00',
        'type' => 'once',
    ]);

    expect($reminder)->toBeInstanceOf(Reminder::class)
        ->and($reminder->quest_id)->toBe($quest->id)
        ->and($reminder->type)->toBe('once')
        ->and($reminder->remind_at->format('Y-m-d H:i'))->toBe('2026-03-01 09:00');
});

it('updates an existing reminder when id provided', function () {
    $quest = Quest::factory()->create();
    $existing = Reminder::factory()->create(['quest_id' => $quest->id]);

    $reminder = (new SaveReminder)->handle($quest, [
        'id' => $existing->id,
        'remind_at' => '2026-04-01 10:00',
        'type' => 'once',
    ]);

    expect($reminder->id)->toBe($existing->id)
        ->and($reminder->remind_at->format('Y-m-d H:i'))->toBe('2026-04-01 10:00')
        ->and(Reminder::count())->toBe(1);
});

it('validates remind_at is required', function () {
    $quest = Quest::factory()->create();

    (new SaveReminder)->handle($quest, ['type' => 'once']);
})->throws(Illuminate\Validation\ValidationException::class);

it('validates type is required', function () {
    $quest = Quest::factory()->create();

    (new SaveReminder)->handle($quest, ['remind_at' => '2026-03-01 09:00']);
})->throws(Illuminate\Validation\ValidationException::class);

it('deletes a reminder', function () {
    $reminder = Reminder::factory()->create();

    (new DeleteReminder)->handle($reminder);

    expect(Reminder::find($reminder->id))->toBeNull();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/ReminderActionsTest.php`

**Step 3: Write the actions**

`SaveReminder.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Enums\ReminderType;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

final readonly class SaveReminder
{
    public function handle(Quest $quest, array $data): Reminder
    {
        $validated = Validator::make($data, [
            'id' => ['nullable', 'integer', 'exists:quest_reminders,id'],
            'remind_at' => ['required', 'date'],
            'type' => ['required', 'string', 'in:once,cron'],
            'recurrence_pattern' => ['nullable', 'string'],
        ])->validate();

        return Reminder::query()->updateOrCreate(
            ['id' => $validated['id'] ?? null],
            [
                'quest_id' => $quest->id,
                'remind_at' => $validated['remind_at'],
                'type' => $validated['type'],
                'recurrence_pattern' => $validated['recurrence_pattern'] ?? null,
                'last_processed_at' => null,
            ],
        );
    }
}
```

`DeleteReminder.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Reminder;

final readonly class DeleteReminder
{
    public function handle(Reminder $reminder): void
    {
        $reminder->delete();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/ReminderActionsTest.php`

```
git add modules/Holocron/Quest/Actions/SaveReminder.php modules/Holocron/Quest/Actions/DeleteReminder.php modules/Holocron/Quest/Tests/Actions/ReminderActionsTest.php
git commit -m "feat: add reminder actions with tests"
```

---

### Task 12: SaveRecurrence + DeleteRecurrence Actions

**Files:**
- Create: `modules/Holocron/Quest/Actions/SaveRecurrence.php`
- Create: `modules/Holocron/Quest/Actions/DeleteRecurrence.php`
- Test: `modules/Holocron/Quest/Tests/Actions/RecurrenceActionsTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\DeleteRecurrence;
use Modules\Holocron\Quest\Actions\SaveRecurrence;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

it('creates a recurrence for a quest', function () {
    $quest = Quest::factory()->create();

    $recurrence = (new SaveRecurrence)->handle($quest, [
        'every_x_days' => 7,
        'recurrence_type' => QuestRecurrence::TYPE_RECURRENCE_BASED,
    ]);

    expect($recurrence)->toBeInstanceOf(QuestRecurrence::class)
        ->and($recurrence->every_x_days)->toBe(7)
        ->and($recurrence->recurrence_type)->toBe(QuestRecurrence::TYPE_RECURRENCE_BASED);
});

it('updates an existing recurrence', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create(['quest_id' => $quest->id, 'every_x_days' => 3]);

    $recurrence = (new SaveRecurrence)->handle($quest, [
        'every_x_days' => 14,
        'recurrence_type' => QuestRecurrence::TYPE_COMPLETION_BASED,
    ]);

    expect($recurrence->every_x_days)->toBe(14)
        ->and($recurrence->recurrence_type)->toBe(QuestRecurrence::TYPE_COMPLETION_BASED)
        ->and(QuestRecurrence::where('quest_id', $quest->id)->count())->toBe(1);
});

it('validates every_x_days is required', function () {
    $quest = Quest::factory()->create();

    (new SaveRecurrence)->handle($quest, [
        'recurrence_type' => QuestRecurrence::TYPE_RECURRENCE_BASED,
    ]);
})->throws(Illuminate\Validation\ValidationException::class);

it('validates recurrence_type is valid', function () {
    $quest = Quest::factory()->create();

    (new SaveRecurrence)->handle($quest, [
        'every_x_days' => 7,
        'recurrence_type' => 'invalid',
    ]);
})->throws(Illuminate\Validation\ValidationException::class);

it('deletes a recurrence', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create(['quest_id' => $quest->id]);

    (new DeleteRecurrence)->handle($quest);

    expect($quest->recurrence()->count())->toBe(0);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/RecurrenceActionsTest.php`

**Step 3: Write the actions**

`SaveRecurrence.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

final readonly class SaveRecurrence
{
    public function handle(Quest $quest, array $data): QuestRecurrence
    {
        $validated = Validator::make($data, [
            'every_x_days' => ['required', 'integer', 'min:1'],
            'recurrence_type' => ['required', 'string', 'in:' . QuestRecurrence::TYPE_RECURRENCE_BASED . ',' . QuestRecurrence::TYPE_COMPLETION_BASED],
            'ends_at' => ['nullable', 'date'],
        ])->validate();

        return $quest->recurrence()->updateOrCreate([], [
            'every_x_days' => $validated['every_x_days'],
            'recurrence_type' => $validated['recurrence_type'],
            'last_recurred_at' => today(),
            'ends_at' => $validated['ends_at'] ?? null,
        ]);
    }
}
```

`DeleteRecurrence.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Modules\Holocron\Quest\Models\Quest;

final readonly class DeleteRecurrence
{
    public function handle(Quest $quest): void
    {
        $quest->recurrence()->delete();
    }
}
```

**Step 4: Run test + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/RecurrenceActionsTest.php`

```
git add modules/Holocron/Quest/Actions/SaveRecurrence.php modules/Holocron/Quest/Actions/DeleteRecurrence.php modules/Holocron/Quest/Tests/Actions/RecurrenceActionsTest.php
git commit -m "feat: add recurrence actions with tests"
```

---

### Task 13: Run all Phase 1 tests

**Step 1: Run all action tests**

Run: `php artisan test modules/Holocron/Quest/Tests/Actions/`
Expected: All tests pass

**Step 2: Run all existing quest tests to verify no regressions**

Run: `php artisan test modules/Holocron/Quest/Tests/`
Expected: All tests pass (existing + new)

---

## Phase 2: Livewire Refactor

### Task 14: Refactor Show.php to use Actions

**Files:**
- Modify: `modules/Holocron/Quest/Livewire/Show.php`

**Step 1: Run existing Show tests to establish baseline**

Run: `php artisan test modules/Holocron/Quest/Tests/ tests/Feature/QuestAttachmentTest.php`
Expected: All pass

**Step 2: Refactor Show.php**

Replace business logic methods with action delegation. Key changes:

In `updating()` — delegate to `UpdateQuest`:
```php
public function updating(string $property, mixed $value): void
{
    if (! in_array($property, ['name', 'description', 'date'])) {
        return;
    }

    $this->validateOnly($property);

    (new UpdateQuest)->handle($this->quest, [$property => $value]);

    $this->reset($property);
}
```

In `updatedNewAttachments()` — delegate to `AddQuestAttachment`:
```php
public function updatedNewAttachments(): void
{
    if (! $this->newAttachments) {
        return;
    }

    foreach ($this->newAttachments as $attachment) {
        (new AddQuestAttachment)->handle($this->quest, $attachment);
    }

    $this->quest->refresh();
    $this->reset('newAttachments');
}
```

In `removeAttachment()` — delegate to `RemoveQuestAttachment`:
```php
public function removeAttachment(string $path): void
{
    (new RemoveQuestAttachment)->handle($this->quest, $path);
}
```

In `toggleComplete()` — delegate to `ToggleQuestComplete`:
```php
public function toggleComplete(): void
{
    (new ToggleQuestComplete)->handle($this->quest);
}
```

In `toggleIsNote()` — delegate to `UpdateQuest`:
```php
public function toggleIsNote(): void
{
    (new UpdateQuest)->handle($this->quest, ['is_note' => ! $this->quest->is_note]);
}
```

In `move()` — delegate to `MoveQuest`:
```php
public function move(?int $id): void
{
    if (is_null($id)) {
        return;
    }

    (new MoveQuest)->handle($this->quest, ['quest_id' => $id]);

    Flux::modal('parent-search')->close();
}
```

In `print()` — delegate to `PrintQuest`:
```php
public function print(): void
{
    (new PrintQuest)->handle($this->quest);
}
```

In `addQuest()` — delegate to `CreateQuest`:
```php
public function addQuest(?string $name = null): void
{
    if (is_null($name)) {
        $this->validateOnly('questDraft');
    }

    (new CreateQuest)->handle([
        'quest_id' => $this->quest->id,
        'name' => $name ?? $this->questDraft,
    ]);

    $this->reset(['questDraft']);
}
```

In `deleteQuest()` — delegate to `DeleteQuest`:
```php
public function deleteQuest(int $id): void
{
    (new DeleteQuest)->handle(Quest::findOrFail($id));
}
```

Add required use statements at top of file:
```php
use Modules\Holocron\Quest\Actions\AddQuestAttachment;
use Modules\Holocron\Quest\Actions\CreateQuest;
use Modules\Holocron\Quest\Actions\DeleteQuest;
use Modules\Holocron\Quest\Actions\MoveQuest;
use Modules\Holocron\Quest\Actions\PrintQuest;
use Modules\Holocron\Quest\Actions\RemoveQuestAttachment;
use Modules\Holocron\Quest\Actions\ToggleQuestComplete;
use Modules\Holocron\Quest\Actions\UpdateQuest;
```

Remove these imports that are no longer needed:
- `use Illuminate\Support\Facades\Storage;` (moved to RemoveQuestAttachment)
- `use Illuminate\Support\Collection;` (no longer used directly)

**Step 3: Run tests to verify no regressions**

Run: `php artisan test modules/Holocron/Quest/Tests/ tests/Feature/QuestAttachmentTest.php`
Expected: All pass

**Step 4: Commit**

```
git add modules/Holocron/Quest/Livewire/Show.php
git commit -m "refactor: delegate Show.php business logic to action classes"
```

---

### Task 15: Refactor WithNotes trait to use Actions

**Files:**
- Modify: `modules/Holocron/Quest/Livewire/WithNotes.php`

**Step 1: Refactor WithNotes.php**

Replace `addNote()` and `deleteNote()` with action delegation. The AI streaming (`ask()`) stays untouched.

```php
public function addNote(): void
{
    $this->validateOnly('noteDraft');

    (new CreateNote)->handle($this->quest, ['content' => $this->noteDraft]);

    $this->reset(['noteDraft']);

    if ($this->chat) {
        $note = (new CreateNote)->handle($this->quest, [
            'role' => 'assistant',
        ]);

        $this->js('$wire.ask(' . $note->id . ')');
    }
}

public function deleteNote(int $id): void
{
    (new DeleteNote)->handle(Note::findOrFail($id));
}
```

Add use statements:
```php
use Modules\Holocron\Quest\Actions\CreateNote;
use Modules\Holocron\Quest\Actions\DeleteNote;
```

**Note:** The `CreateNote` action requires `content` as a required field, but the assistant note created in chat mode initially has no content (it gets filled by streaming). We need to adjust `CreateNote` to allow nullable content OR create the assistant note directly. Since the assistant note is part of the AI workflow (which stays in Livewire), create it directly here:

```php
if ($this->chat) {
    $note = $this->quest->notes()->create([
        'role' => 'assistant',
        'created_at' => now()->addSecond(),
    ]);

    $this->js('$wire.ask(' . $note->id . ')');
}
```

This keeps the AI-specific note creation out of the action (consistent with the decision to skip AI).

**Step 2: Run tests**

Run: `php artisan test modules/Holocron/Quest/Tests/QuestTest.php`
Expected: All pass

**Step 3: Commit**

```
git add modules/Holocron/Quest/Livewire/WithNotes.php
git commit -m "refactor: delegate WithNotes business logic to action classes"
```

---

### Task 16: Refactor WithLinks trait to use Actions

**Files:**
- Modify: `modules/Holocron/Quest/Livewire/WithLinks.php`

**Step 1: Refactor WithLinks.php**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Livewire\Attributes\Validate;
use Modules\Holocron\Quest\Actions\AddQuestLink;
use Modules\Holocron\Quest\Actions\DeleteQuestLink;

trait WithLinks
{
    #[Validate('required')]
    #[Validate('url')]
    public string $linkDraft = '';

    public function addLink(): void
    {
        $this->validateOnly('linkDraft');

        (new AddQuestLink)->handle($this->quest, ['url' => $this->linkDraft]);

        $this->reset(['linkDraft']);
    }

    public function deleteLink(int $pivotId): void
    {
        (new DeleteQuestLink)->handle($this->quest, $pivotId);
    }
}
```

**Step 2: Run tests**

Run: `php artisan test modules/Holocron/Quest/Tests/QuestTest.php modules/Holocron/Quest/Tests/QuestShowTest.php`
Expected: All pass

**Step 3: Commit**

```
git add modules/Holocron/Quest/Livewire/WithLinks.php
git commit -m "refactor: delegate WithLinks business logic to action classes"
```

---

### Task 17: Refactor WithReminders trait to use Actions

**Files:**
- Modify: `modules/Holocron/Quest/Livewire/WithReminders.php`

**Step 1: Refactor WithReminders.php**

Replace `updateReminder()` and `deleteReminder()`:

```php
public function updateReminder(): void
{
    $this->validateOnly('reminderDate');
    $this->validateOnly('reminderTime');

    (new SaveReminder)->handle($this->quest, [
        'id' => $this->editingReminderId,
        'remind_at' => "{$this->reminderDate} {$this->reminderTime}",
        'type' => 'once',
    ]);

    $this->reset(['reminderDate', 'reminderTime', 'editingReminderId']);
}

public function deleteReminder(int $id): void
{
    (new DeleteReminder)->handle(Reminder::findOrFail($id));

    if ($this->editingReminderId === $id) {
        $this->reset(['reminderDate', 'reminderTime', 'editingReminderId']);
    }
}
```

Add use statements:
```php
use Modules\Holocron\Quest\Actions\DeleteReminder as DeleteReminderAction;
use Modules\Holocron\Quest\Actions\SaveReminder;
```

Note: If there's a name collision with `DeleteReminder` action and `Reminder` model usage, alias the action.

Actually, looking at the trait more carefully, the import is `Modules\Holocron\Quest\Models\Reminder` for `findOrFail` and `editReminder`. The action import is `Modules\Holocron\Quest\Actions\DeleteReminder`. No collision since they're different namespaces — just import both:

```php
use Modules\Holocron\Quest\Actions\DeleteReminder as DeleteReminderAction;
use Modules\Holocron\Quest\Actions\SaveReminder;
use Modules\Holocron\Quest\Models\Reminder;
```

And call: `(new DeleteReminderAction)->handle(Reminder::findOrFail($id));`

**Step 2: Run tests**

Run: `php artisan test modules/Holocron/Quest/Tests/`
Expected: All pass

**Step 3: Commit**

```
git add modules/Holocron/Quest/Livewire/WithReminders.php
git commit -m "refactor: delegate WithReminders business logic to action classes"
```

---

### Task 18: Refactor WithRecurrence trait to use Actions

**Files:**
- Modify: `modules/Holocron/Quest/Livewire/WithRecurrence.php`

**Step 1: Refactor WithRecurrence.php**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Modules\Holocron\Quest\Actions\DeleteRecurrence as DeleteRecurrenceAction;
use Modules\Holocron\Quest\Actions\SaveRecurrence;
use Modules\Holocron\Quest\Models\QuestRecurrence;

trait WithRecurrence
{
    public int $recurrenceDays = 1;

    public string $recurrenceType = QuestRecurrence::TYPE_RECURRENCE_BASED;

    public ?string $recurrenceEndsAt = null;

    public function mountWithRecurrence(): void
    {
        if ($this->quest->recurrence) {
            $this->recurrenceDays = $this->quest->recurrence->every_x_days;
            $this->recurrenceType = $this->quest->recurrence->recurrence_type;
            $this->recurrenceEndsAt = $this->quest->recurrence->ends_at?->format('Y-m-d');
        }
    }

    public function saveRecurrence(): void
    {
        (new SaveRecurrence)->handle($this->quest, [
            'every_x_days' => $this->recurrenceDays,
            'recurrence_type' => $this->recurrenceType,
            'ends_at' => $this->recurrenceEndsAt,
        ]);
    }

    public function deleteRecurrence(): void
    {
        (new DeleteRecurrenceAction)->handle($this->quest);
        $this->reset(['recurrenceDays', 'recurrenceType', 'recurrenceEndsAt']);
    }
}
```

**Step 2: Run tests**

Run: `php artisan test modules/Holocron/Quest/Tests/`
Expected: All pass

**Step 3: Commit**

```
git add modules/Holocron/Quest/Livewire/WithRecurrence.php
git commit -m "refactor: delegate WithRecurrence business logic to action classes"
```

---

### Task 19: Refactor Item.php to use Actions

**Files:**
- Modify: `modules/Holocron/Quest/Livewire/Components/Item.php`

**Step 1: Refactor Item.php**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\Quest\Actions\DeleteQuest;
use Modules\Holocron\Quest\Actions\PrintQuest;
use Modules\Holocron\Quest\Actions\ToggleAcceptQuest;
use Modules\Holocron\Quest\Actions\ToggleQuestComplete;
use Modules\Holocron\Quest\Models\Quest;

class Item extends Component
{
    public Quest $quest;

    public bool $showParent = true;

    public ?string $selectedDate = null;

    public function toggleComplete(): void
    {
        (new ToggleQuestComplete)->handle($this->quest);
    }

    public function toggleAccept(): void
    {
        (new ToggleAcceptQuest)->handle($this->quest);
        $this->dispatch('quest:accepted');
    }

    public function print(): void
    {
        (new PrintQuest)->handle($this->quest);
    }

    public function deleteQuest(int $id): void
    {
        (new DeleteQuest)->handle(Quest::findOrFail($id));
        $this->dispatch('quest:deleted');
        $this->skipRender();
    }

    public function render(): View
    {
        return view('holocron-quest::components.item');
    }
}
```

**Step 2: Run tests**

Run: `php artisan test modules/Holocron/Quest/Tests/QuestTest.php`
Expected: All pass (the "can delete a quest" test uses Item component)

**Step 3: Commit**

```
git add modules/Holocron/Quest/Livewire/Components/Item.php
git commit -m "refactor: delegate Item.php business logic to action classes"
```

---

### Task 20: Run full test suite for Phase 2

**Step 1: Run all quest-related tests**

Run: `php artisan test modules/Holocron/Quest/Tests/ tests/Feature/QuestAttachmentTest.php`
Expected: All pass

**Step 2: Run the complete test suite**

Run: `php artisan test`
Expected: All pass

---

## Phase 3: API Layer

### Task 21: API Resources

**Files:**
- Create: `modules/Holocron/Quest/Resources/QuestResource.php`
- Create: `modules/Holocron/Quest/Resources/NoteResource.php`
- Create: `modules/Holocron/Quest/Resources/ReminderResource.php`
- Create: `modules/Holocron/Quest/Resources/QuestRecurrenceResource.php`
- Create: `modules/Holocron/Quest/Resources/WebpageResource.php`

**Step 1: Create the resources**

`QuestResource.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Quest\Models\Quest */
final class QuestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quest_id' => $this->quest_id,
            'name' => $this->name,
            'description' => $this->description,
            'date' => $this->date?->format('Y-m-d'),
            'daily' => $this->daily,
            'is_note' => $this->is_note,
            'accepted' => $this->accepted,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'should_be_printed' => $this->should_be_printed,
            'attachments' => $this->attachments,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'children' => QuestResource::collection($this->whenLoaded('children')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
            'webpages' => WebpageResource::collection($this->whenLoaded('webpages')),
            'reminders' => ReminderResource::collection($this->whenLoaded('reminders')),
            'recurrence' => new QuestRecurrenceResource($this->whenLoaded('recurrence')),
        ];
    }
}
```

`NoteResource.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Quest\Models\Note */
final class NoteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quest_id' => $this->quest_id,
            'content' => $this->content,
            'role' => $this->role,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

`ReminderResource.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Quest\Models\Reminder */
final class ReminderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quest_id' => $this->quest_id,
            'type' => $this->type,
            'remind_at' => $this->remind_at->toIso8601String(),
            'recurrence_pattern' => $this->recurrence_pattern,
            'last_processed_at' => $this->last_processed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

`QuestRecurrenceResource.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Quest\Models\QuestRecurrence */
final class QuestRecurrenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quest_id' => $this->quest_id,
            'every_x_days' => $this->every_x_days,
            'recurrence_type' => $this->recurrence_type,
            'last_recurred_at' => $this->last_recurred_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

`WebpageResource.php`:
```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Modules\Holocron\Bookmarks\Models\Webpage */
final class WebpageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'title' => $this->whenPivotLoaded('quest_webpage', fn () => $this->pivot->title ?? $this->title),
            'pivot_id' => $this->whenPivotLoaded('quest_webpage', fn () => $this->pivot->id),
        ];
    }
}
```

**Step 2: Commit**

```
git add modules/Holocron/Quest/Resources/
git commit -m "feat: add API resources for quest module"
```

---

### Task 22: QuestController + Routes + Tests

**Files:**
- Create: `modules/Holocron/Quest/Controller/Api/QuestController.php`
- Create: `modules/Holocron/Quest/Routes/api.php`
- Modify: `modules/Holocron/Quest/QuestServiceProvider.php` (add `loadRoutesFrom` for api.php)
- Test: `modules/Holocron/Quest/Tests/Api/QuestApiTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Quest;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer ' . config('auth.bearer_token')];
});

it('requires authentication', function () {
    $this->getJson('/api/holocron/quests')->assertUnauthorized();
});

it('lists quests', function () {
    Quest::factory()->count(3)->create();

    $this->withHeaders($this->headers)
        ->getJson('/api/holocron/quests')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a quest', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/holocron/quests', ['name' => 'New Quest'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'New Quest');
});

it('validates name on create', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/holocron/quests', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('shows a quest', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $quest->id);
});

it('shows a quest with relationships', function () {
    $quest = Quest::factory()->create();
    Quest::factory()->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}?include=children")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.children');
});

it('updates a quest', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->patchJson("/api/holocron/quests/{$quest->id}", ['name' => 'Updated'])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated');
});

it('deletes a quest', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}")
        ->assertNoContent();

    expect(Quest::find($quest->id))->toBeNull();
});

it('toggles quest complete', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/complete")
        ->assertSuccessful();

    expect($quest->fresh()->completed_at)->not->toBeNull();
});

it('moves a quest', function () {
    $quest = Quest::factory()->create();
    $newParent = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/move", ['quest_id' => $newParent->id])
        ->assertSuccessful()
        ->assertJsonPath('data.quest_id', $newParent->id);
});

it('prints a quest', function () {
    $quest = Quest::factory()->create(['should_be_printed' => false]);

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/print")
        ->assertSuccessful();

    expect($quest->fresh()->should_be_printed)->toBeTrue();
});

it('toggles quest accept', function () {
    $quest = Quest::factory()->create(['date' => null]);

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/accept")
        ->assertSuccessful();

    expect($quest->fresh()->date)->not->toBeNull();
});

it('returns 404 for nonexistent quest', function () {
    $this->withHeaders($this->headers)
        ->getJson('/api/holocron/quests/99999')
        ->assertNotFound();
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Api/QuestApiTest.php`

**Step 3: Write the controller**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Holocron\Quest\Actions\CreateQuest;
use Modules\Holocron\Quest\Actions\DeleteQuest;
use Modules\Holocron\Quest\Actions\MoveQuest;
use Modules\Holocron\Quest\Actions\PrintQuest;
use Modules\Holocron\Quest\Actions\ToggleAcceptQuest;
use Modules\Holocron\Quest\Actions\ToggleQuestComplete;
use Modules\Holocron\Quest\Actions\UpdateQuest;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\QuestResource;

final class QuestController
{
    public function index(): AnonymousResourceCollection
    {
        return QuestResource::collection(Quest::all());
    }

    public function store(Request $request): QuestResource
    {
        $quest = (new CreateQuest)->handle($request->all());

        return (new QuestResource($quest))
            ->additional(['status' => 'created'])
            ->response()
            ->setStatusCode(201)
            |> fn ($response) => new QuestResource($quest);
    }

    public function show(Request $request, Quest $quest): QuestResource
    {
        $includes = array_filter(explode(',', $request->query('include', '')));
        $allowed = ['children', 'notes', 'webpages', 'reminders', 'recurrence'];
        $validIncludes = array_intersect($includes, $allowed);

        if (! empty($validIncludes)) {
            $quest->load($validIncludes);
        }

        return new QuestResource($quest);
    }

    public function update(Request $request, Quest $quest): QuestResource
    {
        $updated = (new UpdateQuest)->handle($quest, $request->all());

        return new QuestResource($updated);
    }

    public function destroy(Quest $quest): JsonResponse
    {
        (new DeleteQuest)->handle($quest);

        return response()->json(null, 204);
    }

    public function complete(Quest $quest): QuestResource
    {
        $quest = (new ToggleQuestComplete)->handle($quest);

        return new QuestResource($quest);
    }

    public function move(Request $request, Quest $quest): QuestResource
    {
        $quest = (new MoveQuest)->handle($quest, $request->all());

        return new QuestResource($quest);
    }

    public function print(Quest $quest): QuestResource
    {
        $quest = (new PrintQuest)->handle($quest);

        return new QuestResource($quest);
    }

    public function accept(Quest $quest): QuestResource
    {
        $quest = (new ToggleAcceptQuest)->handle($quest);

        return new QuestResource($quest);
    }
}
```

**Note:** The `store` method above has a pipe operator issue. Simpler approach:

```php
public function store(Request $request): JsonResponse
{
    $quest = (new CreateQuest)->handle($request->all());

    return (new QuestResource($quest))
        ->response()
        ->setStatusCode(201);
}
```

**Step 4: Create routes file**

`modules/Holocron/Quest/Routes/api.php`:
```php
<?php

declare(strict_types=1);

use App\Http\Middleware\BearerToken;
use Illuminate\Support\Facades\Route;
use Modules\Holocron\Quest\Controller\Api\QuestController;

Route::middleware(BearerToken::class)
    ->name('holocron.api.quests.')
    ->prefix('api/holocron/quests')
    ->group(function () {
        Route::get('/', [QuestController::class, 'index'])->name('index');
        Route::post('/', [QuestController::class, 'store'])->name('store');
        Route::get('/{quest}', [QuestController::class, 'show'])->name('show');
        Route::patch('/{quest}', [QuestController::class, 'update'])->name('update');
        Route::delete('/{quest}', [QuestController::class, 'destroy'])->name('destroy');
        Route::post('/{quest}/complete', [QuestController::class, 'complete'])->name('complete');
        Route::post('/{quest}/move', [QuestController::class, 'move'])->name('move');
        Route::post('/{quest}/print', [QuestController::class, 'print'])->name('print');
        Route::post('/{quest}/accept', [QuestController::class, 'accept'])->name('accept');
    });
```

**Step 5: Update QuestServiceProvider**

Add this line after the existing `loadRoutesFrom`:
```php
$this->loadRoutesFrom(__DIR__.'/Routes/api.php');
```

**Step 6: Run tests**

Run: `php artisan test modules/Holocron/Quest/Tests/Api/QuestApiTest.php`

**Step 7: Commit**

```
git add modules/Holocron/Quest/Controller/Api/QuestController.php modules/Holocron/Quest/Routes/api.php modules/Holocron/Quest/QuestServiceProvider.php modules/Holocron/Quest/Tests/Api/QuestApiTest.php
git commit -m "feat: add quest API endpoints with tests"
```

---

### Task 23: QuestAttachmentController + Tests

**Files:**
- Create: `modules/Holocron/Quest/Controller/Api/QuestAttachmentController.php`
- Modify: `modules/Holocron/Quest/Routes/api.php`
- Test: `modules/Holocron/Quest/Tests/Api/QuestAttachmentApiTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Quest\Models\Quest;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer ' . config('auth.bearer_token')];
});

it('uploads an attachment', function () {
    Storage::fake('public');

    $quest = Quest::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/attachments", [
            'file' => $file,
        ])
        ->assertSuccessful();

    $quest->refresh();
    expect($quest->attachments)->toHaveCount(1);
    Storage::disk('public')->assertExists($quest->attachments->first());
});

it('removes an attachment', function () {
    Storage::fake('public');

    $path = UploadedFile::fake()->image('photo.jpg')->store('quests', 'public');
    $quest = Quest::factory()->create(['attachments' => [$path]]);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/attachments", [
            'path' => $path,
        ])
        ->assertNoContent();

    $quest->refresh();
    expect($quest->attachments)->toHaveCount(0);
    Storage::disk('public')->assertMissing($path);
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test modules/Holocron/Quest/Tests/Api/QuestAttachmentApiTest.php`

**Step 3: Write the controller**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Holocron\Quest\Actions\AddQuestAttachment;
use Modules\Holocron\Quest\Actions\RemoveQuestAttachment;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\QuestResource;

final class QuestAttachmentController
{
    public function store(Request $request, Quest $quest): QuestResource
    {
        $request->validate(['file' => ['required', 'file']]);

        $updated = (new AddQuestAttachment)->handle($quest, $request->file('file'));

        return new QuestResource($updated);
    }

    public function destroy(Request $request, Quest $quest): JsonResponse
    {
        $request->validate(['path' => ['required', 'string']]);

        (new RemoveQuestAttachment)->handle($quest, $request->input('path'));

        return response()->json(null, 204);
    }
}
```

**Step 4: Add routes to api.php**

Inside the existing group, add:
```php
Route::post('/{quest}/attachments', [QuestAttachmentController::class, 'store'])->name('attachments.store');
Route::delete('/{quest}/attachments', [QuestAttachmentController::class, 'destroy'])->name('attachments.destroy');
```

Add use statement: `use Modules\Holocron\Quest\Controller\Api\QuestAttachmentController;`

**Step 5: Run tests + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Api/QuestAttachmentApiTest.php`

```
git add modules/Holocron/Quest/Controller/Api/QuestAttachmentController.php modules/Holocron/Quest/Routes/api.php modules/Holocron/Quest/Tests/Api/QuestAttachmentApiTest.php
git commit -m "feat: add quest attachment API endpoints with tests"
```

---

### Task 24: QuestNoteController + Tests

**Files:**
- Create: `modules/Holocron/Quest/Controller/Api/QuestNoteController.php`
- Modify: `modules/Holocron/Quest/Routes/api.php`
- Test: `modules/Holocron/Quest/Tests/Api/QuestNoteApiTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer ' . config('auth.bearer_token')];
});

it('lists notes for a quest', function () {
    $quest = Quest::factory()->create();
    Note::factory()->for($quest)->count(3)->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/notes")
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a note', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/notes", [
            'content' => 'Test note',
        ])
        ->assertCreated()
        ->assertJsonPath('data.content', 'Test note');
});

it('validates content on create', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/notes", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

it('deletes a note', function () {
    $quest = Quest::factory()->create();
    $note = Note::factory()->for($quest)->create();

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/notes/{$note->id}")
        ->assertNoContent();

    expect(Note::find($note->id))->toBeNull();
});
```

**Step 2: Write the controller**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Holocron\Quest\Actions\CreateNote;
use Modules\Holocron\Quest\Actions\DeleteNote;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\NoteResource;

final class QuestNoteController
{
    public function index(Quest $quest): AnonymousResourceCollection
    {
        return NoteResource::collection($quest->notes);
    }

    public function store(Request $request, Quest $quest): JsonResponse
    {
        $note = (new CreateNote)->handle($quest, $request->all());

        return (new NoteResource($note))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Quest $quest, Note $note): JsonResponse
    {
        (new DeleteNote)->handle($note);

        return response()->json(null, 204);
    }
}
```

**Step 3: Add routes**

```php
Route::get('/{quest}/notes', [QuestNoteController::class, 'index'])->name('notes.index');
Route::post('/{quest}/notes', [QuestNoteController::class, 'store'])->name('notes.store');
Route::delete('/{quest}/notes/{note}', [QuestNoteController::class, 'destroy'])->name('notes.destroy');
```

Add use statement: `use Modules\Holocron\Quest\Controller\Api\QuestNoteController;`

**Step 4: Run tests + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Api/QuestNoteApiTest.php`

```
git add modules/Holocron/Quest/Controller/Api/QuestNoteController.php modules/Holocron/Quest/Routes/api.php modules/Holocron/Quest/Tests/Api/QuestNoteApiTest.php
git commit -m "feat: add quest note API endpoints with tests"
```

---

### Task 25: QuestLinkController + Tests

**Files:**
- Create: `modules/Holocron/Quest/Controller/Api/QuestLinkController.php`
- Modify: `modules/Holocron/Quest/Routes/api.php`
- Test: `modules/Holocron/Quest/Tests/Api/QuestLinkApiTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Models\Quest;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer ' . config('auth.bearer_token')];
});

it('lists links for a quest', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Test']);

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/links")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('adds a link to a quest', function () {
    Bus::fake();

    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/links", [
            'url' => 'https://example.com',
        ])
        ->assertCreated();

    expect($quest->webpages()->count())->toBe(1);
    Bus::assertDispatched(CrawlWebpageInformation::class);
});

it('validates url on add', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/links", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['url']);
});

it('deletes a link', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Test']);

    $pivotId = $quest->webpages()->first()->pivot->id;

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/links/{$pivotId}")
        ->assertNoContent();

    expect($quest->webpages()->count())->toBe(0);
    expect(Webpage::find($webpage->id))->not->toBeNull();
});
```

**Step 2: Write the controller**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Holocron\Quest\Actions\AddQuestLink;
use Modules\Holocron\Quest\Actions\DeleteQuestLink;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\QuestResource;
use Modules\Holocron\Quest\Resources\WebpageResource;

final class QuestLinkController
{
    public function index(Quest $quest): AnonymousResourceCollection
    {
        return WebpageResource::collection($quest->webpages);
    }

    public function store(Request $request, Quest $quest): JsonResponse
    {
        $updated = (new AddQuestLink)->handle($quest, $request->all());

        return (new QuestResource($updated))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Quest $quest, int $pivotId): JsonResponse
    {
        (new DeleteQuestLink)->handle($quest, $pivotId);

        return response()->json(null, 204);
    }
}
```

**Step 3: Add routes**

```php
Route::get('/{quest}/links', [QuestLinkController::class, 'index'])->name('links.index');
Route::post('/{quest}/links', [QuestLinkController::class, 'store'])->name('links.store');
Route::delete('/{quest}/links/{pivotId}', [QuestLinkController::class, 'destroy'])->name('links.destroy');
```

Add use statement: `use Modules\Holocron\Quest\Controller\Api\QuestLinkController;`

**Step 4: Run tests + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Api/QuestLinkApiTest.php`

```
git add modules/Holocron/Quest/Controller/Api/QuestLinkController.php modules/Holocron/Quest/Routes/api.php modules/Holocron/Quest/Tests/Api/QuestLinkApiTest.php
git commit -m "feat: add quest link API endpoints with tests"
```

---

### Task 26: QuestReminderController + Tests

**Files:**
- Create: `modules/Holocron/Quest/Controller/Api/QuestReminderController.php`
- Modify: `modules/Holocron/Quest/Routes/api.php`
- Test: `modules/Holocron/Quest/Tests/Api/QuestReminderApiTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer ' . config('auth.bearer_token')];
});

it('lists reminders for a quest', function () {
    $quest = Quest::factory()->create();
    Reminder::factory()->count(2)->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/reminders")
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('creates a reminder', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/reminders", [
            'remind_at' => '2026-03-01 09:00',
            'type' => 'once',
        ])
        ->assertCreated();

    expect($quest->reminders()->count())->toBe(1);
});

it('validates required fields', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/reminders", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['remind_at', 'type']);
});

it('deletes a reminder', function () {
    $quest = Quest::factory()->create();
    $reminder = Reminder::factory()->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/reminders/{$reminder->id}")
        ->assertNoContent();

    expect(Reminder::find($reminder->id))->toBeNull();
});
```

**Step 2: Write the controller**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Holocron\Quest\Actions\DeleteReminder;
use Modules\Holocron\Quest\Actions\SaveReminder;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;
use Modules\Holocron\Quest\Resources\ReminderResource;

final class QuestReminderController
{
    public function index(Quest $quest): AnonymousResourceCollection
    {
        return ReminderResource::collection($quest->reminders);
    }

    public function store(Request $request, Quest $quest): JsonResponse
    {
        $reminder = (new SaveReminder)->handle($quest, $request->all());

        return (new ReminderResource($reminder))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Quest $quest, Reminder $reminder): JsonResponse
    {
        (new DeleteReminder)->handle($reminder);

        return response()->json(null, 204);
    }
}
```

**Step 3: Add routes**

```php
Route::get('/{quest}/reminders', [QuestReminderController::class, 'index'])->name('reminders.index');
Route::post('/{quest}/reminders', [QuestReminderController::class, 'store'])->name('reminders.store');
Route::delete('/{quest}/reminders/{reminder}', [QuestReminderController::class, 'destroy'])->name('reminders.destroy');
```

Add use statement: `use Modules\Holocron\Quest\Controller\Api\QuestReminderController;`

**Step 4: Run tests + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Api/QuestReminderApiTest.php`

```
git add modules/Holocron/Quest/Controller/Api/QuestReminderController.php modules/Holocron/Quest/Routes/api.php modules/Holocron/Quest/Tests/Api/QuestReminderApiTest.php
git commit -m "feat: add quest reminder API endpoints with tests"
```

---

### Task 27: QuestRecurrenceController + Tests

**Files:**
- Create: `modules/Holocron/Quest/Controller/Api/QuestRecurrenceController.php`
- Modify: `modules/Holocron/Quest/Routes/api.php`
- Test: `modules/Holocron/Quest/Tests/Api/QuestRecurrenceApiTest.php`

**Step 1: Write the test**

```php
<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer ' . config('auth.bearer_token')];
});

it('shows recurrence for a quest', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/recurrence")
        ->assertSuccessful()
        ->assertJsonPath('data.quest_id', $quest->id);
});

it('returns null when no recurrence exists', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/recurrence")
        ->assertSuccessful()
        ->assertJsonPath('data', null);
});

it('creates a recurrence', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/recurrence", [
            'every_x_days' => 7,
            'recurrence_type' => 'recurrence_based',
        ])
        ->assertCreated()
        ->assertJsonPath('data.every_x_days', 7);
});

it('validates required fields', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/recurrence", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['every_x_days', 'recurrence_type']);
});

it('deletes a recurrence', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/recurrence")
        ->assertNoContent();

    expect($quest->recurrence()->count())->toBe(0);
});
```

**Step 2: Write the controller**

```php
<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Controller\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Holocron\Quest\Actions\DeleteRecurrence;
use Modules\Holocron\Quest\Actions\SaveRecurrence;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Resources\QuestRecurrenceResource;

final class QuestRecurrenceController
{
    public function show(Quest $quest): QuestRecurrenceResource
    {
        return new QuestRecurrenceResource($quest->recurrence);
    }

    public function store(Request $request, Quest $quest): JsonResponse
    {
        $recurrence = (new SaveRecurrence)->handle($quest, $request->all());

        return (new QuestRecurrenceResource($recurrence))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Quest $quest): JsonResponse
    {
        (new DeleteRecurrence)->handle($quest);

        return response()->json(null, 204);
    }
}
```

**Step 3: Add routes**

```php
Route::get('/{quest}/recurrence', [QuestRecurrenceController::class, 'show'])->name('recurrence.show');
Route::post('/{quest}/recurrence', [QuestRecurrenceController::class, 'store'])->name('recurrence.store');
Route::delete('/{quest}/recurrence', [QuestRecurrenceController::class, 'destroy'])->name('recurrence.destroy');
```

Add use statement: `use Modules\Holocron\Quest\Controller\Api\QuestRecurrenceController;`

**Step 4: Run tests + commit**

Run: `php artisan test modules/Holocron/Quest/Tests/Api/QuestRecurrenceApiTest.php`

```
git add modules/Holocron/Quest/Controller/Api/QuestRecurrenceController.php modules/Holocron/Quest/Routes/api.php modules/Holocron/Quest/Tests/Api/QuestRecurrenceApiTest.php
git commit -m "feat: add quest recurrence API endpoints with tests"
```

---

### Task 28: Run Pint + Full Test Suite

**Step 1: Run Pint to fix formatting**

Run: `vendor/bin/pint --dirty`

**Step 2: Commit any formatting fixes**

```
git add -u && git commit -m "style: fix formatting with pint"
```

**Step 3: Run the complete test suite**

Run: `php artisan test`
Expected: All tests pass

**Step 4: If any failures, fix and re-run**

Focus on the specific failing test, fix, re-run that test, then full suite again.
