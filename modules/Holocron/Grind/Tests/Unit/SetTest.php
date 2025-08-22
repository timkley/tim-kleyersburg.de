<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Set;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\Grind\Models\WorkoutExercise;

test('relations', function () {
    $set = Set::factory()->create();

    expect($set->workoutExercise)->toBeInstanceOf(WorkoutExercise::class);
    expect($set->workoutExercise->exercise)->toBeInstanceOf(Exercise::class);
    expect($set->workoutExercise->workout)->toBeInstanceOf(Workout::class);
});

test('siblings scope', function () {
    $workoutExercise = WorkoutExercise::factory()->create();
    $sets = Set::factory()->count(3)->for($workoutExercise)->create();

    $otherWorkoutExercise = WorkoutExercise::factory()->create();
    Set::factory()->for($otherWorkoutExercise)->create();

    $firstSet = $sets->first();
    $siblings = $firstSet->siblings()->get();

    expect($siblings)->toHaveCount(3);
    expect($siblings->pluck('id')->all())->toEqual($sets->pluck('id')->all());
});
