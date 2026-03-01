<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Livewire\Show;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;
use Modules\Holocron\Quest\Models\Reminder;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());
});

// --- Basic Show ---

it('renders the show page', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->assertStatus(200)
        ->assertSet('name', $quest->name)
        ->assertSet('description', $quest->description);
});

it('mounts with quest data', function () {
    $quest = Quest::factory()->create([
        'date' => '2026-03-01',
    ]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->assertSet('name', $quest->name)
        ->assertSet('description', $quest->description)
        ->assertSet('date', '2026-03-01');
});

it('mounts with null date', function () {
    $quest = Quest::factory()->create(['date' => null]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->assertSet('date', null);
});

it('sets quest name on component', function () {
    $quest = Quest::factory()->create(['name' => 'My Quest Title']);

    Livewire::test(Show::class, ['quest' => $quest])
        ->assertSet('name', 'My Quest Title');
});

// --- Updating properties ---

it('updates quest description via updating hook', function () {
    $quest = Quest::factory()->create(['description' => 'Old description']);

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('description', 'New description');

    expect($quest->fresh()->description)->toBe('New description');
});

it('updates quest date via updating hook', function () {
    $quest = Quest::factory()->create(['date' => '2026-01-01']);

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('date', '2026-06-15');

    expect($quest->fresh()->date->format('Y-m-d'))->toBe('2026-06-15');
});

it('ignores updating for non-tracked properties', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('showAllSubquests', true)
        ->assertHasNoErrors();
});

// --- Toggle Complete ---

it('toggles quest complete', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('toggleComplete');

    expect($quest->fresh()->completed_at)->not->toBeNull();
});

it('toggles quest uncomplete', function () {
    $quest = Quest::factory()->create(['completed_at' => now()]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('toggleComplete');

    expect($quest->fresh()->completed_at)->toBeNull();
});

// --- Toggle Is Note ---

it('toggles is_note flag', function () {
    $quest = Quest::factory()->create(['is_note' => false]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('toggleIsNote');

    expect($quest->fresh()->is_note)->toBeTrue();
});

// --- Print ---

it('prints a quest', function () {
    $quest = Quest::factory()->create(['should_be_printed' => false]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('print');

    expect((bool) $quest->fresh()->should_be_printed)->toBeTrue();
});

// --- Add Sub-Quest ---

it('adds a sub-quest', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('questDraft', 'New Sub Quest')
        ->call('addQuest')
        ->assertSet('questDraft', '');

    $this->assertDatabaseHas('quests', [
        'name' => 'New Sub Quest',
        'quest_id' => $quest->id,
    ]);
});

it('adds a sub-quest with explicit name parameter', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('addQuest', 'Suggested Quest');

    $this->assertDatabaseHas('quests', [
        'name' => 'Suggested Quest',
        'quest_id' => $quest->id,
    ]);
});

it('validates quest draft on add', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('questDraft', 'ab')
        ->call('addQuest')
        ->assertHasErrors('questDraft');
});

// --- Delete Sub-Quest ---

it('deletes a sub-quest', function () {
    $quest = Quest::factory()->create();
    $child = Quest::factory()->create(['quest_id' => $quest->id]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('deleteQuest', $child->id);

    expect(Quest::find($child->id))->toBeNull();
});

// --- Move Quest ---

it('moves a quest to a new parent', function () {
    $quest = Quest::factory()->create();
    $newParent = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('move', $newParent->id);

    expect($quest->fresh()->quest_id)->toBe($newParent->id);
});

it('does nothing when moving with null id', function () {
    $quest = Quest::factory()->create(['quest_id' => null]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('move', null);

    expect($quest->fresh()->quest_id)->toBeNull();
});

// --- Attachments ---

it('uploads and removes attachments', function () {
    Storage::fake('public');

    $quest = Quest::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg');

    $component = Livewire::test(Show::class, ['quest' => $quest])
        ->set('newAttachments', [$file]);

    $quest->refresh();
    expect($quest->attachments)->toHaveCount(1);

    $path = $quest->attachments->first();
    Storage::disk('public')->assertExists($path);

    $component->call('removeAttachment', $path);

    $quest->refresh();
    expect($quest->attachments)->toHaveCount(0);
    Storage::disk('public')->assertMissing($path);
});

it('does nothing when newAttachments is empty', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('newAttachments', [])
        ->assertSet('newAttachments', []);
});

// --- Show All Subquests ---

it('shows only uncompleted sub-quests by default', function () {
    $quest = Quest::factory()->create();
    $incomplete = Quest::factory()->create([
        'quest_id' => $quest->id,
        'completed_at' => null,
    ]);
    $completed = Quest::factory()->create([
        'quest_id' => $quest->id,
        'completed_at' => now(),
    ]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->assertSee($incomplete->name)
        ->assertDontSee($completed->name);
});

it('shows all sub-quests when toggled', function () {
    $quest = Quest::factory()->create();
    $completed = Quest::factory()->create([
        'quest_id' => $quest->id,
        'completed_at' => now(),
    ]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('showAllSubquests', true)
        ->assertSee($completed->name);
});

// --- WithLinks trait ---

it('adds a link', function () {
    Bus::fake();

    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('linkDraft', 'https://example.com')
        ->call('addLink')
        ->assertSet('linkDraft', '');

    expect($quest->webpages()->count())->toBe(1);
    Bus::assertDispatched(CrawlWebpageInformation::class);
});

it('validates link url', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('linkDraft', 'not-a-url')
        ->call('addLink')
        ->assertHasErrors('linkDraft');
});

it('validates link url is required', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('linkDraft', '')
        ->call('addLink')
        ->assertHasErrors('linkDraft');
});

it('deletes a link', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Test']);
    $pivotId = $quest->webpages()->first()->pivot->id;

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('deleteLink', $pivotId);

    expect($quest->fresh()->webpages)->toHaveCount(0);
    expect($webpage->fresh())->not->toBeNull();
});

// --- WithNotes trait ---

it('adds a note', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('noteDraft', 'This is a note')
        ->call('addNote')
        ->assertSet('noteDraft', '');

    $this->assertDatabaseHas('quest_notes', [
        'quest_id' => $quest->id,
        'content' => 'This is a note',
        'role' => 'user',
    ]);
});

it('creates assistant note when chat mode is enabled', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('chat', true)
        ->set('noteDraft', 'A chat message')
        ->call('addNote')
        ->assertSet('noteDraft', '');

    // Should create a user note and an assistant note
    expect($quest->notes()->where('role', 'user')->count())->toBe(1)
        ->and($quest->notes()->where('role', 'assistant')->count())->toBe(1);
});

it('validates note draft', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('noteDraft', 'ab')
        ->call('addNote')
        ->assertHasErrors('noteDraft');
});

it('validates note draft is required', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('noteDraft', '')
        ->call('addNote')
        ->assertHasErrors('noteDraft');
});

it('deletes a note', function () {
    $quest = Quest::factory()->create();
    $note = Note::factory()->for($quest)->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('deleteNote', $note->id);

    expect(Note::find($note->id))->toBeNull();
});

// --- WithRecurrence trait ---

it('mounts with existing recurrence data', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create([
        'quest_id' => $quest->id,
        'every_x_days' => 7,
        'recurrence_type' => QuestRecurrence::TYPE_COMPLETION_BASED,
        'ends_at' => '2026-12-31',
    ]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->assertSet('recurrenceDays', 7)
        ->assertSet('recurrenceType', QuestRecurrence::TYPE_COMPLETION_BASED)
        ->assertSet('recurrenceEndsAt', '2026-12-31');
});

it('saves a recurrence', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('recurrenceDays', 3)
        ->set('recurrenceType', QuestRecurrence::TYPE_RECURRENCE_BASED)
        ->call('saveRecurrence');

    $recurrence = $quest->recurrence()->first();
    expect($recurrence)->not->toBeNull()
        ->and($recurrence->every_x_days)->toBe(3)
        ->and($recurrence->recurrence_type)->toBe(QuestRecurrence::TYPE_RECURRENCE_BASED);
});

it('deletes a recurrence', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create(['quest_id' => $quest->id]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('deleteRecurrence');

    expect($quest->recurrence()->count())->toBe(0);
});

it('resets recurrence properties on delete', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create([
        'quest_id' => $quest->id,
        'every_x_days' => 7,
    ]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('deleteRecurrence')
        ->assertSet('recurrenceDays', 1)
        ->assertSet('recurrenceType', QuestRecurrence::TYPE_RECURRENCE_BASED)
        ->assertSet('recurrenceEndsAt', null);
});

// --- WithReminders trait ---

it('mounts with reminder defaults', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->assertSet('reminderDate', now()->format('Y-m-d'));
});

it('saves a reminder', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('reminderDate', '2026-03-15')
        ->set('reminderTime', '14:00')
        ->call('updateReminder');

    $reminder = $quest->reminders()->first();
    expect($reminder)->not->toBeNull()
        ->and($reminder->remind_at->format('Y-m-d H:i'))->toBe('2026-03-15 14:00');
});

it('edits a reminder', function () {
    $quest = Quest::factory()->create();
    $reminder = Reminder::factory()->create([
        'quest_id' => $quest->id,
        'remind_at' => '2026-04-01 10:00:00',
    ]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('editReminder', $reminder->id)
        ->assertSet('editingReminderId', $reminder->id)
        ->assertSet('reminderDate', '2026-04-01')
        ->assertSet('reminderTime', '10:00');
});

it('deletes a reminder', function () {
    $quest = Quest::factory()->create();
    $reminder = Reminder::factory()->create(['quest_id' => $quest->id]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('deleteReminder', $reminder->id);

    expect(Reminder::find($reminder->id))->toBeNull();
});

it('resets editing state when deleting the reminder being edited', function () {
    $quest = Quest::factory()->create();
    $reminder = Reminder::factory()->create(['quest_id' => $quest->id]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('editReminder', $reminder->id)
        ->assertSet('editingReminderId', $reminder->id)
        ->call('deleteReminder', $reminder->id)
        ->assertSet('editingReminderId', null);
});

it('does not reset editing state when deleting a different reminder', function () {
    $quest = Quest::factory()->create();
    $reminder1 = Reminder::factory()->create(['quest_id' => $quest->id]);
    $reminder2 = Reminder::factory()->create(['quest_id' => $quest->id]);

    Livewire::test(Show::class, ['quest' => $quest])
        ->call('editReminder', $reminder1->id)
        ->call('deleteReminder', $reminder2->id)
        ->assertSet('editingReminderId', $reminder1->id);
});

it('computes active reminders', function () {
    $quest = Quest::factory()->create();

    // Active (not processed)
    Reminder::factory()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->addHour(),
    ]);

    // Already processed
    Reminder::factory()->processed()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->subHour(),
    ]);

    $component = Livewire::test(Show::class, ['quest' => $quest]);

    expect($component->instance()->activeReminders())->toHaveCount(1);
});

it('validates reminder date is required', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('reminderDate', '')
        ->set('reminderTime', '14:00')
        ->call('updateReminder')
        ->assertHasErrors('reminderDate');
});

it('validates reminder time is required', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Show::class, ['quest' => $quest])
        ->set('reminderDate', '2026-03-15')
        ->set('reminderTime', '')
        ->call('updateReminder')
        ->assertHasErrors('reminderTime');
});
