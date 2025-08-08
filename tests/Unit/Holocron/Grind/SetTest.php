<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Set;
use App\Models\Holocron\Grind\Workout;
use App\Models\Holocron\Grind\WorkoutExercise;

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
