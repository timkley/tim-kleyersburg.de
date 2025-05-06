<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Set;
use App\Models\Holocron\Grind\Workout;

test('relations', function () {
    $set = Set::factory()->create();

    expect($set->exercise)->toBeInstanceOf(Exercise::class);
    expect($set->workout)->toBeInstanceOf(Workout::class);
});
