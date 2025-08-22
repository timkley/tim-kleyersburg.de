<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Workout;

test('relations', function () {
    $workout = Workout::factory()->create();

    expect($workout->plan)->toBeInstanceOf(Plan::class);
});
