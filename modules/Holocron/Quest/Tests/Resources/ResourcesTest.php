<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;
use Modules\Holocron\Quest\Models\Reminder;
use Modules\Holocron\Quest\Resources\NoteResource;
use Modules\Holocron\Quest\Resources\QuestRecurrenceResource;
use Modules\Holocron\Quest\Resources\QuestResource;
use Modules\Holocron\Quest\Resources\ReminderResource;
use Modules\Holocron\Quest\Resources\WebpageResource;

// NoteResource

it('transforms a note into an array', function () {
    $note = Note::factory()->create([
        'content' => 'Test content',
        'role' => 'user',
    ]);

    $resource = (new NoteResource($note))->toArray(new Request);

    expect($resource)
        ->toHaveKeys(['id', 'quest_id', 'content', 'role', 'created_at', 'updated_at'])
        ->and($resource['id'])->toBe($note->id)
        ->and($resource['quest_id'])->toBe($note->quest_id)
        ->and($resource['content'])->toBe('Test content')
        ->and($resource['role'])->toBe('user')
        ->and($resource['created_at'])->toBe($note->created_at->toIso8601String())
        ->and($resource['updated_at'])->toBe($note->updated_at->toIso8601String());
});

// QuestRecurrenceResource

it('transforms a quest recurrence into an array', function () {
    $recurrence = QuestRecurrence::factory()->create([
        'every_x_days' => 7,
        'recurrence_type' => QuestRecurrence::TYPE_RECURRENCE_BASED,
        'last_recurred_at' => '2025-06-01 12:00:00',
        'ends_at' => '2025-12-31 23:59:59',
    ]);

    $resource = (new QuestRecurrenceResource($recurrence))->toArray(new Request);

    expect($resource)
        ->toHaveKeys(['id', 'quest_id', 'every_x_days', 'recurrence_type', 'last_recurred_at', 'ends_at', 'created_at', 'updated_at'])
        ->and($resource['id'])->toBe($recurrence->id)
        ->and($resource['every_x_days'])->toBe(7)
        ->and($resource['recurrence_type'])->toBe('recurrence_based')
        ->and($resource['last_recurred_at'])->toBeString()
        ->and($resource['ends_at'])->toBeString();
});

it('returns empty array for null recurrence resource', function () {
    $request = Request::create('/');
    $result = (new QuestRecurrenceResource(null))->toArray($request);

    expect($result)->toEqual([]);
});

it('handles nullable dates in recurrence resource', function () {
    $recurrence = QuestRecurrence::factory()->create([
        'last_recurred_at' => null,
        'ends_at' => null,
    ]);

    $resource = (new QuestRecurrenceResource($recurrence))->toArray(new Request);

    expect($resource['last_recurred_at'])->toBeNull()
        ->and($resource['ends_at'])->toBeNull();
});

// QuestResource

it('transforms a quest into an array', function () {
    $quest = Quest::factory()->create([
        'name' => 'Test Quest',
        'description' => 'A description',
        'date' => '2025-06-01',
        'daily' => false,
        'is_note' => false,
        'completed_at' => null,
        'should_be_printed' => false,
        'attachments' => ['file1.jpg'],
    ]);

    $resource = (new QuestResource($quest))->toArray(new Request);

    expect($resource)
        ->toHaveKeys(['id', 'quest_id', 'name', 'description', 'date', 'daily', 'is_note', 'accepted', 'completed_at', 'should_be_printed', 'attachments', 'created_at', 'updated_at'])
        ->and($resource['id'])->toBe($quest->id)
        ->and($resource['name'])->toBe('Test Quest')
        ->and($resource['description'])->toBe('A description')
        ->and($resource['date'])->toBe('2025-06-01')
        ->and($resource['daily'])->toBeFalse()
        ->and($resource['is_note'])->toBeFalse()
        ->and($resource['completed_at'])->toBeNull()
        ->and($resource['should_be_printed'])->toBeFalsy();
});

it('includes completed_at as ISO 8601 when present', function () {
    $quest = Quest::factory()->create(['completed_at' => '2025-06-15 14:30:00']);

    $resource = (new QuestResource($quest))->toArray(new Request);

    expect($resource['completed_at'])->toBeString()
        ->and($resource['completed_at'])->toContain('2025-06-15');
});

it('formats date as Y-m-d', function () {
    $quest = Quest::factory()->create(['date' => '2025-06-01']);

    $resource = (new QuestResource($quest))->toArray(new Request);

    expect($resource['date'])->toBe('2025-06-01');
});

it('returns null date for quests without a date', function () {
    $quest = Quest::factory()->create(['date' => null]);

    $resource = (new QuestResource($quest))->toArray(new Request);

    expect($resource['date'])->toBeNull();
});

it('includes loaded children in quest resource', function () {
    $parent = Quest::factory()->create();
    Quest::factory()->count(2)->create(['quest_id' => $parent->id]);

    $parent->load('children');
    $resource = (new QuestResource($parent))->toArray(new Request);

    expect($resource['children'])->toHaveCount(2);
});

it('includes loaded notes in quest resource', function () {
    $quest = Quest::factory()->create();
    Note::factory()->count(2)->for($quest)->create();

    $quest->load('notes');
    $resource = (new QuestResource($quest))->toArray(new Request);

    expect($resource['notes'])->toHaveCount(2);
});

it('includes loaded webpages in quest resource', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Link']);

    $quest->load('webpages');
    $resource = (new QuestResource($quest))->toArray(new Request);

    expect($resource['webpages'])->toHaveCount(1);
});

it('includes loaded reminders in quest resource', function () {
    $quest = Quest::factory()->create();
    Reminder::factory()->count(2)->for($quest)->create();

    $quest->load('reminders');
    $resource = (new QuestResource($quest))->toArray(new Request);

    expect($resource['reminders'])->toHaveCount(2);
});

it('includes loaded recurrence in quest resource', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->for($quest)->create();

    $quest->load('recurrence');
    $request = Request::create('/');
    $resource = (new QuestResource($quest))->toArray($request);

    $recurrence = $resource['recurrence'] instanceof QuestRecurrenceResource
        ? $resource['recurrence']->toArray($request)
        : $resource['recurrence'];

    expect($recurrence)->not->toBeNull()
        ->and($recurrence)->toHaveKeys(['id', 'quest_id', 'every_x_days']);
});

// ReminderResource

it('transforms a reminder into an array', function () {
    $reminder = Reminder::factory()->cron()->create([
        'remind_at' => '2025-06-01 09:00:00',
        'recurrence_pattern' => '0 9 * * *',
    ]);

    $resource = (new ReminderResource($reminder))->toArray(new Request);

    expect($resource)
        ->toHaveKeys(['id', 'quest_id', 'type', 'remind_at', 'recurrence_pattern', 'last_processed_at', 'created_at', 'updated_at'])
        ->and($resource['id'])->toBe($reminder->id)
        ->and($resource['type'])->not->toBeNull()
        ->and($resource['remind_at'])->toBeString()
        ->and($resource['recurrence_pattern'])->toBe('0 9 * * *')
        ->and($resource['last_processed_at'])->toBeNull();
});

it('includes last_processed_at when reminder was processed', function () {
    $reminder = Reminder::factory()->processed()->create();

    $resource = (new ReminderResource($reminder))->toArray(new Request);

    expect($resource['last_processed_at'])->toBeString();
});

// WebpageResource

it('transforms a webpage into an array', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create(['url' => 'https://example.com', 'title' => 'Example']);
    $quest->webpages()->attach($webpage, ['title' => 'Custom Title']);

    $loadedWebpage = $quest->webpages()->first();
    $resource = (new WebpageResource($loadedWebpage))->toArray(new Request);

    expect($resource)
        ->toHaveKeys(['id', 'url', 'title', 'pivot_id'])
        ->and($resource['id'])->toBe($webpage->id)
        ->and($resource['url'])->toBe('https://example.com')
        ->and($resource['title'])->toBe('Custom Title')
        ->and($resource['pivot_id'])->not->toBeNull();
});

it('falls back to webpage title when pivot title is null', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create(['url' => 'https://example.com', 'title' => 'Original Title']);
    $quest->webpages()->attach($webpage, ['title' => null]);

    $loadedWebpage = $quest->webpages()->first();
    $resource = (new WebpageResource($loadedWebpage))->toArray(new Request);

    expect($resource['title'])->toBe('Original Title');
});
