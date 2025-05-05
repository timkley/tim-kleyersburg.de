<?php

declare(strict_types=1);

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Plan;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.grind.plans.index'))
        ->assertRedirect();
});

it('works', function () {
    Livewire::test('holocron.grind.plans')
        ->assertSuccessful();
});

it('can add a plan', function () {
    Livewire::test('holocron.grind.plans')
        ->set('name', 'asdf')
        ->call('submit')
        ->assertHasNoErrors();
});

it('can show a plan', function () {
    $plan = Plan::factory()->create();

    actingAs(User::factory()->create());
    get(route('holocron.grind.plans.show', 1))
        ->assertSuccessful();

    Livewire::test('holocron.grind.plans.show', [$plan->id])
        ->assertSuccessful();
});

it('can delete a plan', function () {
    Plan::factory()->create();

    Livewire::test('holocron.grind.plans')
        ->call('delete', 1);

    expect(Plan::count())->toBe(0);
});

it('can add exercises to a plan', function () {
    $plan = Plan::factory()->create();
    $exercise = Exercise::factory()->create();

    $component = Livewire::test('holocron.grind.plans.show', [$plan->id])
        ->set('exerciseId', $exercise->id)
        ->set('sets', 3)
        ->set('minReps', 6)
        ->set('maxReps', 10)
        ->set('order', 2);

    $component->call('addExercise', 1)
        ->assertHasNoErrors();

    expect($plan->exercises->count())->toBe(1);

    $component->call('addExercise', 1);
    expect($plan->fresh()->exercises->count())->toBe(1);

    $exercise = $plan->fresh()->exercises->first();
    expect($exercise->pivot->sets)->toBe(3);
    expect($exercise->pivot->min_reps)->toBe(6);
    expect($exercise->pivot->max_reps)->toBe(10);
    expect($exercise->pivot->order)->toBe(2);
});
