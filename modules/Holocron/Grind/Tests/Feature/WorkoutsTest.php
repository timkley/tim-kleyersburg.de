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

it('prefills the workout with sets of a previous workout for the same plan', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

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

    $workout = Workout::find(1);

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->set('weight', 3)
        ->set('reps', 3)
        ->call('recordSet')
        ->call('finish');

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', 1)
        ->assertRedirect(route('holocron.grind.workouts.show', [2]));

    $workout2 = Workout::find(2);

    expect($workout2->sets->count())->toBe($workout->sets->count());
});

it('skips finished workouts without sets when prefilling', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $plan = Plan::factory()->hasAttached(
        Exercise::factory()->count(3),
        [
            'sets' => 3,
            'min_reps' => 3,
            'max_reps' => 3,
            'order' => 1,
        ]
    )->create();

    // First, create a workout with sets
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', 1)
        ->assertRedirect(route('holocron.grind.workouts.show', [1]));

    $workoutWithSets = Workout::find(1);

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workoutWithSets])
        ->set('weight', 10)
        ->set('reps', 5)
        ->call('recordSet')
        ->call('finish');

    // Create a second workout but don't add any sets (simulate removed exercises)
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', 1)
        ->assertRedirect(route('holocron.grind.workouts.show', [2]));

    $workoutWithoutSets = Workout::find(2);
    // Finish this workout without adding sets
    $workoutWithoutSets->update(['finished_at' => now()]);

    // Now start a third workout - it should prefill from the first workout (with sets), not the second (without sets)
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', 1)
        ->assertRedirect(route('holocron.grind.workouts.show', [3]));

    $newWorkout = Workout::find(3);

    // The new workout should have sets prefilled from the first workout, not be empty
    expect($newWorkout->sets->count())->toBe($workoutWithSets->sets->count());
    expect($newWorkout->sets->count())->toBeGreaterThan(0);
});
