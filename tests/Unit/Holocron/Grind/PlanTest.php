<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Plan;

test('relations', function () {
    $plan = Plan::factory()->hasAttached(
        Exercise::factory()->count(3),
        [
            'sets' => 1,
            'min_reps' => 2,
            'max_reps' => 6,
            'order' => 1,
        ]
    )->create();

    expect($plan->exercises->first())->toBeInstanceOf(Exercise::class);
});
