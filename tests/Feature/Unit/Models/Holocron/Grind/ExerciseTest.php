<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Plan;

test('relations', function () {
    $exercise = Exercise::factory()->hasAttached(Plan::factory(), [
        'sets' => 1,
        'min_reps' => 2,
        'max_reps' => 6,
        'order' => 1,
    ])->create();

    expect($exercise->plans->first())->toBeInstanceOf(Plan::class);
});
