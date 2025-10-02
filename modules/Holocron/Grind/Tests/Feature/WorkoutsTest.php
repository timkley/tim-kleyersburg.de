<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.grind.workouts.index'))
        ->assertRedirect();
});

it('works', function () {
    Livewire::test('holocron.grind.workouts.index')
        ->assertSuccessful();
});

it('can start a workout', function () {
    $plan = Plan::factory()->hasAttached(
        Exercise::factory()->count(3),
        [
            'sets' => 3,
            'min_reps' => 3,
            'max_reps' => 3,
            'order' => 1,
        ]
    )->create();

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', 1)
        ->assertRedirect(route('holocron.grind.workouts.show', [1]));

    $workout = Workout::first();

    expect($workout)->toBeInstanceOf(Workout::class);
    expect($workout->started_at)->not()->toBeNull();
});

it('prefills the workout with sets based on latest exercise performance', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise,
        [
            'sets' => 2,
            'min_reps' => 5,
            'max_reps' => 10,
            'order' => 1,
        ]
    )->create();

    // Start first workout
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [1]));

    $firstWorkout = Workout::find(1);

    // Add sets to first workout and finish it
    Livewire::test('holocron.grind.workouts.show', ['workout' => $firstWorkout])
        ->set('weight', 20)
        ->set('reps', 8)
        ->call('recordSet')
        ->set('weight', 22)
        ->set('reps', 6)
        ->call('recordSet')
        ->call('finish');

    // Start second workout
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [2]));

    $secondWorkout = Workout::find(2);

    // Second workout should have sets prepopulated from first workout
    expect($secondWorkout->sets->count())->toBe(2);

    // Verify that both weight values from the first workout are present
    $weights = $secondWorkout->sets->pluck('weight')->sort()->values();
    $reps = $secondWorkout->sets->pluck('reps')->sort()->values();

    expect($weights->toArray())->toBe([20.0, 22.0]);
    expect($reps->toArray())->toBe([6, 8]);
});

it('skips finished workouts without sets when prefilling', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise,
        [
            'sets' => 1,
            'min_reps' => 3,
            'max_reps' => 3,
            'order' => 1,
        ]
    )->create();

    // First, create a workout with sets
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [1]));

    $workoutWithSets = Workout::find(1);

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workoutWithSets])
        ->set('weight', 10)
        ->set('reps', 5)
        ->call('recordSet')
        ->call('finish');

    // Create a second workout but don't add any sets (simulate removed exercises)
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [2]));

    $workoutWithoutSets = Workout::find(2);
    // Finish this workout without adding sets
    $workoutWithoutSets->update(['finished_at' => now()]);

    // Now start a third workout - it should prefill from the first workout (with sets), not the second (without sets)
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [3]));

    $newWorkout = Workout::find(3);

    // The new workout should have sets prepopulated from the first workout (that had sets)
    // Our new logic correctly finds sets from the first workout regardless of the empty second workout
    expect($newWorkout->sets->count())->toBe(1); // Our new logic finds the set from the first workout
    expect($newWorkout->sets->count())->toBeGreaterThan(0);
});

// TODO: Add test for cross-plan exercise prepopulation
// This test would verify that exercises shared between different plans
// get prepopulated from their latest performance regardless of which plan they came from

it('does not prepopulate sets for exercises never performed before', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise,
        [
            'sets' => 3,
            'min_reps' => 5,
            'max_reps' => 10,
            'order' => 1,
        ]
    )->create();

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [1]));

    $workout = Workout::find(1);

    // Should have no sets since exercise was never performed before
    expect($workout->sets->count())->toBe(0);
});
