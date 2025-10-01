<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Set;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\Grind\Models\WorkoutExercise;

test('relations', function () {
    $exercise = Exercise::factory()->hasAttached(Plan::factory(), [
        'sets' => 1,
        'min_reps' => 2,
        'max_reps' => 6,
        'order' => 1,
    ])->create();

    // Create a workout exercise for this exercise
    $workoutExercise = WorkoutExercise::factory()->create([
        'exercise_id' => $exercise->id,
        'workout_id' => Workout::factory()->create()->id,
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    // Create a set linked to the workout exercise
    $set = new Set([
        'reps' => 10,
        'weight' => 50,
    ]);
    $set->workoutExercise()->associate($workoutExercise);
    $set->save();

    expect($exercise->plans->first())->toBeInstanceOf(Plan::class);
    expect($exercise->sets->first())->toBeInstanceOf(Set::class);
});

test('volumePerWorkout returns sets grouped by workout with total volume', function () {
    // Create an exercise
    $exercise = Exercise::factory()->create();

    // Create two workouts
    $workout1 = Workout::factory()->create([
        'started_at' => Carbon::now()->subDays(2),
        'finished_at' => Carbon::now()->subDays(2)->addHour(),
    ]);

    $workout2 = Workout::factory()->create([
        'started_at' => Carbon::now()->subDays(1),
        'finished_at' => Carbon::now()->subDays(1)->addHour(),
    ]);

    // Create workout exercises linking the exercise to the workouts
    $workoutExercise1 = WorkoutExercise::factory()->create([
        'workout_id' => $workout1->id,
        'exercise_id' => $exercise->id,
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    $workoutExercise2 = WorkoutExercise::factory()->create([
        'workout_id' => $workout2->id,
        'exercise_id' => $exercise->id,
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    // Create sets for the first workout
    $set1 = new Set([
        'reps' => 10,
        'weight' => 50,
        'started_at' => Carbon::now()->subDays(2)->addMinutes(5),
        'finished_at' => Carbon::now()->subDays(2)->addMinutes(6),
    ]);
    $set1->workoutExercise()->associate($workoutExercise1);
    $set1->save();

    $set2 = new Set([
        'reps' => 8,
        'weight' => 55,
        'started_at' => Carbon::now()->subDays(2)->addMinutes(8),
        'finished_at' => Carbon::now()->subDays(2)->addMinutes(9),
    ]);
    $set2->workoutExercise()->associate($workoutExercise1);
    $set2->save();

    // Create sets for the second workout
    $set3 = new Set([
        'reps' => 12,
        'weight' => 45,
        'started_at' => Carbon::now()->subDays(1)->addMinutes(5),
        'finished_at' => Carbon::now()->subDays(1)->addMinutes(6),
    ]);
    $set3->workoutExercise()->associate($workoutExercise2);
    $set3->save();

    $set4 = new Set([
        'reps' => 10,
        'weight' => 50,
        'started_at' => Carbon::now()->subDays(1)->addMinutes(8),
        'finished_at' => Carbon::now()->subDays(1)->addMinutes(9),
    ]);
    $set4->workoutExercise()->associate($workoutExercise2);
    $set4->save();

    // Calculate expected volumes
    // For workout1: (10 * 50) + (8 * 55) = 500 + 440 = 940
    // For workout2: (12 * 45) + (10 * 50) = 540 + 500 = 1040

    // Get the volumePerWorkout results
    $volumePerWorkout = $exercise->volumePerWorkout();

    // Assert the collection has 2 items (one for each workout)
    expect($volumePerWorkout)->toHaveCount(2);

    // Check the first workout (should be workout2 since it's more recent and we're ordering by workout_completed_at desc)
    expect($volumePerWorkout[0]->workout_id)->toBe($workout2->id);
    expect($volumePerWorkout[0]->total_volume)->toEqual(1040);

    // Check the second workout (should be workout1)
    expect($volumePerWorkout[1]->workout_id)->toBe($workout1->id);
    expect($volumePerWorkout[1]->total_volume)->toEqual(940);
});

test('volumePerWorkout only includes sets with finished_at not null', function () {
    // Create an exercise
    $exercise = Exercise::factory()->create();

    // Create a workout
    $workout = Workout::factory()->create([
        'finished_at' => Carbon::now(),
    ]);

    // Create workout exercise
    $workoutExercise = WorkoutExercise::factory()->create([
        'workout_id' => $workout->id,
        'exercise_id' => $exercise->id,
        'sets' => 2,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    // Create a completed set
    $completedSet = new Set([
        'reps' => 10,
        'weight' => 50,
        'started_at' => Carbon::now()->subMinutes(10),
        'finished_at' => Carbon::now()->subMinutes(9),
    ]);
    $completedSet->workoutExercise()->associate($workoutExercise);
    $completedSet->save();

    // Create an incomplete set (no finished_at)
    $incompleteSet = new Set([
        'reps' => 8,
        'weight' => 55,
        'started_at' => Carbon::now()->subMinutes(5),
        'finished_at' => null,
    ]);
    $incompleteSet->workoutExercise()->associate($workoutExercise);
    $incompleteSet->save();

    // Get the volumePerWorkout results
    $volumePerWorkout = $exercise->volumePerWorkout();

    // Assert the collection has 1 item (only the completed set should be included)
    expect($volumePerWorkout)->toHaveCount(1);

    // Check the volume (should only include the completed set: 10 * 50 = 500)
    expect($volumePerWorkout[0]->total_volume)->toEqual(500);
});

test('volumePerWorkout includes finished sets from ongoing workouts', function () {
    // Create an exercise
    $exercise = Exercise::factory()->create();

    // Create a finished workout
    $finishedWorkout = Workout::factory()->create([
        'started_at' => Carbon::now()->subDays(2),
        'finished_at' => Carbon::now()->subDays(2)->addHour(),
    ]);

    // Create an ongoing workout (no finished_at)
    $ongoingWorkout = Workout::factory()->create([
        'started_at' => Carbon::now()->subHour(),
        'finished_at' => null,
    ]);

    // Create workout exercises
    $finishedWorkoutExercise = WorkoutExercise::factory()->create([
        'workout_id' => $finishedWorkout->id,
        'exercise_id' => $exercise->id,
        'sets' => 1,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    $ongoingWorkoutExercise = WorkoutExercise::factory()->create([
        'workout_id' => $ongoingWorkout->id,
        'exercise_id' => $exercise->id,
        'sets' => 2,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    // Create a finished set for the finished workout
    $finishedWorkoutSet = new Set([
        'reps' => 10,
        'weight' => 50,
        'started_at' => Carbon::now()->subDays(2)->addMinutes(5),
        'finished_at' => Carbon::now()->subDays(2)->addMinutes(6),
    ]);
    $finishedWorkoutSet->workoutExercise()->associate($finishedWorkoutExercise);
    $finishedWorkoutSet->save();

    // Create a finished set for the ongoing workout
    $ongoingWorkoutFinishedSet = new Set([
        'reps' => 8,
        'weight' => 60,
        'started_at' => Carbon::now()->subMinutes(30),
        'finished_at' => Carbon::now()->subMinutes(29),
    ]);
    $ongoingWorkoutFinishedSet->workoutExercise()->associate($ongoingWorkoutExercise);
    $ongoingWorkoutFinishedSet->save();

    // Create an unfinished set for the ongoing workout
    $ongoingWorkoutUnfinishedSet = new Set([
        'reps' => 12,
        'weight' => 55,
        'started_at' => Carbon::now()->subMinutes(10),
        'finished_at' => null,
    ]);
    $ongoingWorkoutUnfinishedSet->workoutExercise()->associate($ongoingWorkoutExercise);
    $ongoingWorkoutUnfinishedSet->save();

    // Get the volumePerWorkout results
    $volumePerWorkout = $exercise->volumePerWorkout();

    // Assert the collection has 2 items (both workouts should be included)
    expect($volumePerWorkout)->toHaveCount(2);

    // Check that both workouts are included
    $workoutIds = $volumePerWorkout->pluck('workout_id')->toArray();
    expect($workoutIds)->toContain($finishedWorkout->id);
    expect($workoutIds)->toContain($ongoingWorkout->id);

    // Check volumes - ongoing workout should only include finished set: 8 * 60 = 480
    $ongoingWorkoutResult = $volumePerWorkout->firstWhere('workout_id', $ongoingWorkout->id);
    expect($ongoingWorkoutResult->total_volume)->toEqual(480);

    // Check finished workout volume: 10 * 50 = 500
    $finishedWorkoutResult = $volumePerWorkout->firstWhere('workout_id', $finishedWorkout->id);
    expect($finishedWorkoutResult->total_volume)->toEqual(500);
});

test('volumePerWorkout limits results to 30 workouts', function () {
    // Create an exercise
    $exercise = Exercise::factory()->create();

    // Create 35 workouts and sets
    for ($i = 0; $i < 35; $i++) {
        $workout = Workout::factory()->create([
            'started_at' => Carbon::now()->subDays($i),
            'finished_at' => Carbon::now()->subDays($i)->addHour(),
        ]);

        $workoutExercise = WorkoutExercise::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
            'sets' => 1,
            'min_reps' => 10,
            'max_reps' => 10,
            'order' => 1,
        ]);

        $set = new Set([
            'reps' => 10,
            'weight' => 50,
            'started_at' => Carbon::now()->subDays($i)->addMinutes(5),
            'finished_at' => Carbon::now()->subDays($i)->addMinutes(6),
        ]);
        $set->workoutExercise()->associate($workoutExercise);
        $set->save();
    }

    // Get the volumePerWorkout results
    $volumePerWorkout = $exercise->volumePerWorkout();

    // Assert the collection has 30 items (limited by the function)
    expect($volumePerWorkout)->toHaveCount(30);
});
