<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Plan;
use App\Models\Holocron\Grind\Workout;

test('relations', function () {
    $workout = Workout::factory()->create();

    expect($workout->plan)->toBeInstanceOf(Plan::class);
});
