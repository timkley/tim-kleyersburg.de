<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Set;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\Grind\Models\WorkoutExercise;

test('exercise relation', function () {
    $workoutExercise = WorkoutExercise::factory()->create();

    expect($workoutExercise->exercise)->toBeInstanceOf(Exercise::class);
});

test('workout relation', function () {
    $workoutExercise = WorkoutExercise::factory()->create();

    expect($workoutExercise->workout)->toBeInstanceOf(Workout::class);
});

test('sets relation returns child sets', function () {
    $workoutExercise = WorkoutExercise::factory()->create();

    Set::factory()->count(3)->create(['workout_exercise_id' => $workoutExercise->id]);

    $relatedSets = $workoutExercise->sets()->get();

    expect($relatedSets)->toHaveCount(3)
        ->each->toBeInstanceOf(Set::class);
});

test('uses grind_workout_exercises table', function () {
    expect((new WorkoutExercise)->getTable())->toBe('grind_workout_exercises');
});

test('factory creates valid workout exercise', function () {
    $workoutExercise = WorkoutExercise::factory()->create();

    expect($workoutExercise->exists)->toBeTrue()
        ->and($workoutExercise->workout_id)->not->toBeNull()
        ->and($workoutExercise->exercise_id)->not->toBeNull()
        ->and($workoutExercise->order)->not->toBeNull();
});
