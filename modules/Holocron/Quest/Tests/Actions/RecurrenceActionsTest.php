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
    QuestRecurrence::factory()->for($quest)->create(['every_x_days' => 3]);

    $updated = (new SaveRecurrence)->handle($quest, [
        'every_x_days' => 14,
        'recurrence_type' => QuestRecurrence::TYPE_COMPLETION_BASED,
    ]);

    expect($quest->recurrence()->count())->toBe(1)
        ->and($updated->every_x_days)->toBe(14)
        ->and($updated->recurrence_type)->toBe(QuestRecurrence::TYPE_COMPLETION_BASED);
});

it('validates that every_x_days is required', function () {
    $quest = Quest::factory()->create();

    (new SaveRecurrence)->handle($quest, [
        'recurrence_type' => QuestRecurrence::TYPE_RECURRENCE_BASED,
    ]);
})->throws(Illuminate\Validation\ValidationException::class);

it('validates that recurrence_type must be a valid value', function () {
    $quest = Quest::factory()->create();

    (new SaveRecurrence)->handle($quest, [
        'every_x_days' => 7,
        'recurrence_type' => 'invalid_type',
    ]);
})->throws(Illuminate\Validation\ValidationException::class);

it('deletes a recurrence', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->for($quest)->create();

    (new DeleteRecurrence)->handle($quest);

    expect($quest->recurrence()->exists())->toBeFalse();
});
