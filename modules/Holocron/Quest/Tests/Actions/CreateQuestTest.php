<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\CreateQuest;
use Modules\Holocron\Quest\Models\Quest;

it('creates a quest with required fields', function () {
    $quest = (new CreateQuest)->handle(['name' => 'My Quest']);

    expect($quest)->toBeInstanceOf(Quest::class)
        ->and($quest->name)->toBe('My Quest')
        ->and($quest->exists)->toBeTrue();
});

it('creates a quest with all optional fields', function () {
    $parent = Quest::factory()->create();

    $quest = (new CreateQuest)->handle([
        'name' => 'Child Quest',
        'quest_id' => $parent->id,
        'date' => '2025-01-01',
        'daily' => true,
        'is_note' => false,
        'description' => 'A description',
    ]);

    expect($quest->name)->toBe('Child Quest')
        ->and($quest->quest_id)->toBe($parent->id)
        ->and($quest->daily)->toBeTrue()
        ->and($quest->is_note)->toBeFalse()
        ->and($quest->description)->toBe('A description');
});

it('validates that name is required', function () {
    (new CreateQuest)->handle([]);
})->throws(Illuminate\Validation\ValidationException::class);

it('validates that quest_id must exist', function () {
    (new CreateQuest)->handle([
        'name' => 'My Quest',
        'quest_id' => 99999,
    ]);
})->throws(Illuminate\Validation\ValidationException::class);
