<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Set;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\Grind\Models\WorkoutExercise;

test('plan relation', function () {
    $workout = Workout::factory()->create();

    expect($workout->plan)->toBeInstanceOf(Plan::class);
});

test('exercises relation', function () {
    $workout = Workout::factory()->create();

    WorkoutExercise::factory()->count(3)->create([
        'workout_id' => $workout->id,
    ]);

    expect($workout->exercises)->toHaveCount(3)
        ->each->toBeInstanceOf(WorkoutExercise::class);
});

test('exercises are ordered by order column', function () {
    $workout = Workout::factory()->create();

    WorkoutExercise::factory()->create(['workout_id' => $workout->id, 'order' => 3]);
    WorkoutExercise::factory()->create(['workout_id' => $workout->id, 'order' => 1]);
    WorkoutExercise::factory()->create(['workout_id' => $workout->id, 'order' => 2]);

    $orders = $workout->exercises->pluck('order')->all();

    expect($orders)->toBe([1, 2, 3]);
});

test('sets relation returns sets through workout exercises', function () {
    $workout = Workout::factory()->create();
    $workoutExercise = WorkoutExercise::factory()->create(['workout_id' => $workout->id]);

    Set::factory()->count(2)->create(['workout_exercise_id' => $workoutExercise->id]);

    expect($workout->sets)->toHaveCount(2)
        ->each->toBeInstanceOf(Set::class);
});

test('getCurrentExercise returns exercise by current_exercise_id', function () {
    $workout = Workout::factory()->create();

    $we1 = WorkoutExercise::factory()->create(['workout_id' => $workout->id, 'order' => 1]);
    $we2 = WorkoutExercise::factory()->create(['workout_id' => $workout->id, 'order' => 2]);

    $workout->update(['current_exercise_id' => $we2->id]);

    expect($workout->getCurrentExercise()->id)->toBe($we2->id);
});

test('getCurrentExercise falls back to first exercise when id is not found', function () {
    $workout = Workout::factory()->create(['current_exercise_id' => 999]);

    $we1 = WorkoutExercise::factory()->create(['workout_id' => $workout->id, 'order' => 1]);
    WorkoutExercise::factory()->create(['workout_id' => $workout->id, 'order' => 2]);

    expect($workout->getCurrentExercise()->id)->toBe($we1->id);
});

test('getCurrentExercise returns null when no exercises exist', function () {
    $workout = Workout::factory()->create();

    expect($workout->getCurrentExercise())->toBeNull();
});

test('casts started_at and finished_at to datetime', function () {
    $workout = Workout::factory()->create([
        'started_at' => '2025-06-15 10:00:00',
        'finished_at' => '2025-06-15 11:00:00',
    ]);

    expect($workout->started_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($workout->finished_at)->toBeInstanceOf(CarbonImmutable::class);
});
