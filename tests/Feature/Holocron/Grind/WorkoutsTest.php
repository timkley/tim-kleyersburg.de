<?php

declare(strict_types=1);

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
    $workout = Workout::factory()->has(Set::factory()->count(3))->create();
    $plan = $workout->plan;

    expect($workout->sets->count())->toBe(3);

    Livewire::test('holocron.grind.workouts')
        ->call('start', $workout->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [2]));

    $workout2 = Workout::find(2);

    expect($workout2->sets->count())->toBe($workout->sets->count());
});
