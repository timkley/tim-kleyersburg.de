<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
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

test('latestForExercise scope filters by exercise and finished workouts', function () {
    $exercise = Exercise::factory()->create();
    $otherExercise = Exercise::factory()->create();

    $finishedWorkout = Workout::factory()->create(['finished_at' => now()]);
    $unfinishedWorkout = Workout::factory()->create(['finished_at' => null]);

    $weFinished = WorkoutExercise::factory()->create([
        'workout_id' => $finishedWorkout->id,
        'exercise_id' => $exercise->id,
    ]);

    $weUnfinished = WorkoutExercise::factory()->create([
        'workout_id' => $unfinishedWorkout->id,
        'exercise_id' => $exercise->id,
    ]);

    $weOtherExercise = WorkoutExercise::factory()->create([
        'workout_id' => $finishedWorkout->id,
        'exercise_id' => $otherExercise->id,
    ]);

    $matchingSet = Set::factory()->create(['workout_exercise_id' => $weFinished->id]);
    Set::factory()->create(['workout_exercise_id' => $weUnfinished->id]);
    Set::factory()->create(['workout_exercise_id' => $weOtherExercise->id]);

    $results = Set::query()->latestForExercise($exercise->id)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($matchingSet->id);
});

test('latestForExercise scope orders by created_at descending', function () {
    $exercise = Exercise::factory()->create();
    $workout = Workout::factory()->create(['finished_at' => now()]);

    $we = WorkoutExercise::factory()->create([
        'workout_id' => $workout->id,
        'exercise_id' => $exercise->id,
    ]);

    $olderSet = Set::factory()->create([
        'workout_exercise_id' => $we->id,
        'created_at' => now()->subDay(),
    ]);
    $newerSet = Set::factory()->create([
        'workout_exercise_id' => $we->id,
        'created_at' => now(),
    ]);

    $results = Set::query()->latestForExercise($exercise->id)->get();

    expect($results->first()->id)->toBe($newerSet->id)
        ->and($results->last()->id)->toBe($olderSet->id);
});

test('casts started_at and finished_at to datetime', function () {
    $set = Set::factory()->create([
        'started_at' => '2025-06-15 10:00:00',
        'finished_at' => '2025-06-15 10:05:00',
    ]);

    expect($set->started_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($set->finished_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('started_at and finished_at can be null', function () {
    $set = Set::factory()->create([
        'started_at' => null,
        'finished_at' => null,
    ]);

    expect($set->started_at)->toBeNull()
        ->and($set->finished_at)->toBeNull();
});
