<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

it('belongs to a quest', function () {
    $quest = Quest::factory()->create();
    $note = Note::factory()->for($quest)->create();

    expect($note->quest)->toBeInstanceOf(Quest::class)
        ->and($note->quest->id)->toBe($quest->id);
});

it('uses quest_notes table', function () {
    $note = new Note;

    expect($note->getTable())->toBe('quest_notes');
});

it('generates a searchable array', function () {
    $note = Note::factory()->create(['content' => 'Test content']);

    $searchable = $note->toSearchableArray();

    expect($searchable)
        ->toHaveKey('id')
        ->toHaveKey('quest_id')
        ->toHaveKey('content')
        ->toHaveKey('created_at')
        ->and($searchable['id'])->toBeString()
        ->and($searchable['quest_id'])->toBeString()
        ->and($searchable['content'])->toBe('Test content')
        ->and($searchable['created_at'])->toBeInt();
});

it('defaults null content to empty string in searchable array', function () {
    $note = Note::factory()->create(['content' => null]);

    $searchable = $note->toSearchableArray();

    expect($searchable['content'])->toBe('');
});

it('creates notes using the factory', function () {
    $note = Note::factory()->create();

    expect($note)->toBeInstanceOf(Note::class)
        ->and($note->exists)->toBeTrue()
        ->and($note->quest_id)->not->toBeNull();
});
