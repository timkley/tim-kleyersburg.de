<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

it('belongs to a quest', function () {
    $quest = Quest::factory()->create();
    $recurrence = QuestRecurrence::factory()->for($quest)->create();

    expect($recurrence->quest)->toBeInstanceOf(Quest::class)
        ->and($recurrence->quest->id)->toBe($quest->id);
});

it('uses quest_recurrences table', function () {
    $recurrence = new QuestRecurrence;

    expect($recurrence->getTable())->toBe('quest_recurrences');
});

it('defines recurrence type constants', function () {
    expect(QuestRecurrence::TYPE_RECURRENCE_BASED)->toBe('recurrence_based')
        ->and(QuestRecurrence::TYPE_COMPLETION_BASED)->toBe('completion_based');
});

it('casts last_recurred_at as datetime', function () {
    $recurrence = QuestRecurrence::factory()->create([
        'last_recurred_at' => '2025-06-01 12:00:00',
    ]);

    expect($recurrence->last_recurred_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('casts ends_at as datetime', function () {
    $recurrence = QuestRecurrence::factory()->create([
        'ends_at' => '2025-12-31 23:59:59',
    ]);

    expect($recurrence->ends_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('allows null ends_at', function () {
    $recurrence = QuestRecurrence::factory()->create(['ends_at' => null]);

    expect($recurrence->ends_at)->toBeNull();
});

it('creates recurrence using the factory', function () {
    $recurrence = QuestRecurrence::factory()->create();

    expect($recurrence)->toBeInstanceOf(QuestRecurrence::class)
        ->and($recurrence->exists)->toBeTrue()
        ->and($recurrence->every_x_days)->toBe(1)
        ->and($recurrence->recurrence_type)->toBe(QuestRecurrence::TYPE_RECURRENCE_BASED);
});

it('creates completion-based recurrence via factory state', function () {
    $recurrence = QuestRecurrence::factory()->completionBased()->create();

    expect($recurrence->recurrence_type)->toBe(QuestRecurrence::TYPE_COMPLETION_BASED);
});

it('creates recurrence with custom interval via factory state', function () {
    $recurrence = QuestRecurrence::factory()->everyDays(7)->create();

    expect($recurrence->every_x_days)->toBe(7);
});
