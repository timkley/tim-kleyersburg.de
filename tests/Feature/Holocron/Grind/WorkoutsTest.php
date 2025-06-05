<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Plan;
use App\Models\Holocron\Grind\Set;
use App\Models\Holocron\Grind\Workout;

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.grind.workouts.index'))
        ->assertRedirect();
});

it('works', function () {
    Livewire::test('holocron.grind.workouts')
        ->assertSuccessful();
});

it('can start a workout', function () {
    Plan::factory()->create();

    Livewire::test('holocron.grind.workouts')
        ->call('start', 1)
        ->assertRedirect(route('holocron.grind.workouts.show', [1]));

    $workout = Workout::first();

    expect($workout)->toBeInstanceOf(Workout::class);
    expect($workout->started_at)->not()->toBeNull();
});

it('prefills the workout with sets of a previous workout for the same plan', function () {
    // Create a plan with 3 exercises
    $plan = Plan::factory()->create();
    $exercises = Exercise::factory()->count(3)->create();
    $workout = Workout::factory()->create(['plan_id' => $plan->id]);

    // Add exercises to the plan with pivot data
    foreach ($exercises as $index => $exercise) {
        $plan->exercises()->attach($exercise->id, [
            'sets' => 3,
            'min_reps' => 8,
            'max_reps' => 12,
            'order' => $index,
        ]);

        $workout->sets()->create([
            'exercise_id' => $exercise->id,
            'weight' => 80,
            'reps' => 80,
        ]);
    }

    expect($workout->sets->count())->toBe(3);

    Livewire::test('holocron.grind.workouts')
        ->call('start', $workout->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [2]));

    $workout2 = Workout::find(2);

    expect($workout2->sets->count())->toBe($workout->sets->count());
});

it('contains all exercises of the current plan when starting a new workout', function () {
    // Create a plan with 3 exercises
    $plan = Plan::factory()->create();
    $exercises = Exercise::factory()->count(3)->create();

    // Add exercises to the plan with pivot data
    foreach ($exercises as $index => $exercise) {
        $plan->exercises()->attach($exercise->id, [
            'sets' => 3,
            'min_reps' => 8,
            'max_reps' => 12,
            'order' => $index,
        ]);
    }

    // Create a workout for this plan
    $workout = Workout::factory()->create(['plan_id' => $plan->id]);

    // Load the workout with its plan and exercises
    $workout->load('plan.exercises');

    // Verify that the workout's plan has all the exercises
    expect($workout->plan->exercises->count())->toBe(3);

    // Test the Workouts/Show component
    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->assertSuccessful()
        ->assertViewHas('exercises', function ($exercises) use ($plan) {
            return $exercises->count() === $plan->exercises->count();
        });
});

it('shows original exercises after plan is changed', function () {
    // Create a plan with 3 exercises
    $plan = Plan::factory()->create();
    $originalExercises = Exercise::factory()->count(3)->create();

    // Add exercises to the plan with pivot data
    foreach ($originalExercises as $index => $exercise) {
        $plan->exercises()->attach($exercise->id, [
            'sets' => 3,
            'min_reps' => 8,
            'max_reps' => 12,
            'order' => $index,
        ]);
    }

    // Create a workout for this plan
    $workout = Workout::factory()->create(['plan_id' => $plan->id]);

    // Create sets for each exercise in the workout
    foreach ($originalExercises as $exercise) {
        Set::factory()->create([
            'workout_id' => $workout->id,
            'exercise_id' => $exercise->id,
        ]);
    }

    // Mark the workout as finished
    $workout->update(['finished_at' => now()]);

    // Change the plan by removing one exercise and adding a new one
    $plan->exercises()->detach($originalExercises->first()->id);
    $newExercise = Exercise::factory()->create();
    $plan->exercises()->attach($newExercise->id, [
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 3,
    ]);

    // Reload the plan with its exercises
    $plan->load('exercises');

    // Verify that the plan now has different exercises
    expect($plan->exercises->count())->toBe(3);
    expect($plan->exercises->pluck('id')->toArray())->not->toContain($originalExercises->first()->id);
    expect($plan->exercises->pluck('id')->toArray())->toContain($newExercise->id);

    // Test the Workouts/Show component
    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->assertSuccessful()
        ->assertViewHas('exercises', function ($exercises) use ($originalExercises) {
            // The exercises shown should be the original ones that were in the sets
            $exerciseIds = $exercises->pluck('id')->toArray();
            foreach ($originalExercises as $exercise) {
                if (! in_array($exercise->id, $exerciseIds)) {
                    return false;
                }
            }

            return true;
        });
});
