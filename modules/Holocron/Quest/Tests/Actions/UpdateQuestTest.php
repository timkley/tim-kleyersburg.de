<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\UpdateQuest;
use Modules\Holocron\Quest\Models\Quest;

it('updates a quest name', function () {
    $quest = Quest::factory()->create(['name' => 'Old Name']);

    $updated = (new UpdateQuest)->handle($quest, ['name' => 'New Name']);

    expect($updated->name)->toBe('New Name');
});

it('updates multiple fields', function () {
    $quest = Quest::factory()->create(['name' => 'Old Name', 'daily' => false]);

    $updated = (new UpdateQuest)->handle($quest, [
        'name' => 'New Name',
        'description' => 'A new description',
        'daily' => true,
    ]);

    expect($updated->name)->toBe('New Name')
        ->and($updated->description)->toBe('A new description')
        ->and($updated->daily)->toBeTrue();
});

it('allows partial updates without touching other fields', function () {
    $quest = Quest::factory()->create(['name' => 'Original', 'daily' => true]);

    $updated = (new UpdateQuest)->handle($quest, ['description' => 'Just a desc']);

    expect($updated->name)->toBe('Original')
        ->and($updated->daily)->toBeTrue()
        ->and($updated->description)->toBe('Just a desc');
});

it('validates that name must be a string', function () {
    $quest = Quest::factory()->create();

    (new UpdateQuest)->handle($quest, ['name' => ['not', 'a', 'string']]);
})->throws(Illuminate\Validation\ValidationException::class);
