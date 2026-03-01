<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Plan;

test('exercises relation', function () {
    $plan = Plan::factory()->hasAttached(
        Exercise::factory()->count(3),
        [
            'sets' => 1,
            'min_reps' => 2,
            'max_reps' => 6,
            'order' => 1,
        ]
    )->create();

    expect($plan->exercises)->toHaveCount(3)
        ->each->toBeInstanceOf(Exercise::class);
});

test('exercises pivot includes sets, min_reps, max_reps, and order', function () {
    $plan = Plan::factory()->hasAttached(
        Exercise::factory(),
        [
            'sets' => 4,
            'min_reps' => 6,
            'max_reps' => 12,
            'order' => 2,
        ]
    )->create();

    $pivot = $plan->exercises->first()->pivot;

    expect($pivot->sets)->toBe(4)
        ->and($pivot->min_reps)->toBe(6)
        ->and($pivot->max_reps)->toBe(12)
        ->and($pivot->order)->toBe(2);
});

test('exercises are ordered by the order pivot column', function () {
    $plan = Plan::factory()->create();

    $exerciseA = Exercise::factory()->create();
    $exerciseB = Exercise::factory()->create();
    $exerciseC = Exercise::factory()->create();

    $plan->exercises()->attach($exerciseA, ['sets' => 1, 'min_reps' => 5, 'max_reps' => 10, 'order' => 3]);
    $plan->exercises()->attach($exerciseB, ['sets' => 1, 'min_reps' => 5, 'max_reps' => 10, 'order' => 1]);
    $plan->exercises()->attach($exerciseC, ['sets' => 1, 'min_reps' => 5, 'max_reps' => 10, 'order' => 2]);

    $orderedIds = $plan->exercises->pluck('id')->all();

    expect($orderedIds)->toBe([$exerciseB->id, $exerciseC->id, $exerciseA->id]);
});
