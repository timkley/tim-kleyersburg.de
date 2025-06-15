<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Plan;
use App\Models\Holocron\Grind\Workout;
use App\Models\User;

use function Pest\Laravel\actingAs;
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
    $plan = Plan::factory()->hasAttached(
        Exercise::factory()->count(3),
        [
            'sets' => 3,
            'min_reps' => 3,
            'max_reps' => 3,
            'order' => 1,
        ]
    )->create();

    Livewire::test('holocron.grind.workouts')
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

    Livewire::test('holocron.grind.workouts')
        ->call('start', 1)
        ->assertRedirect(route('holocron.grind.workouts.show', [1]));

    $workout = Workout::find(1);

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->set('weight', 3)
        ->set('reps', 3)
        ->call('recordSet')
        ->call('finish');

    Livewire::test('holocron.grind.workouts')
        ->call('start', 1)
        ->assertRedirect(route('holocron.grind.workouts.show', [2]));

    $workout2 = Workout::find(2);

    expect($workout2->sets->count())->toBe($workout->sets->count());
});
