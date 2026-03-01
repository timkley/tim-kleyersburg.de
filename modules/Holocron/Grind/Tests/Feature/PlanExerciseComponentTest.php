<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('renders the exercise component with pivot data', function () {
    $plan = Plan::factory()->create();
    $exercise = Exercise::factory()->create();

    $plan->exercises()->attach($exercise, [
        'sets' => 4,
        'min_reps' => 6,
        'max_reps' => 12,
        'order' => 2,
    ]);

    $exerciseWithPivot = $plan->exercises()->first();

    Livewire::test('holocron.grind.plans.components.exercise', ['exercise' => $exerciseWithPivot])
        ->assertSuccessful()
        ->assertSet('exerciseId', $exercise->id)
        ->assertSet('planId', $plan->id)
        ->assertSet('sets', 4)
        ->assertSet('min_reps', 6)
        ->assertSet('max_reps', 12)
        ->assertSet('order', 2);
});

it('updates pivot sets when property changes', function () {
    $plan = Plan::factory()->create();
    $exercise = Exercise::factory()->create();

    $plan->exercises()->attach($exercise, [
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    $exerciseWithPivot = $plan->exercises()->first();

    Livewire::test('holocron.grind.plans.components.exercise', ['exercise' => $exerciseWithPivot])
        ->set('sets', 5);

    $pivot = $plan->exercises()->first()->pivot;
    expect($pivot->sets)->toBe(5);
});

it('updates pivot min_reps when property changes', function () {
    $plan = Plan::factory()->create();
    $exercise = Exercise::factory()->create();

    $plan->exercises()->attach($exercise, [
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    $exerciseWithPivot = $plan->exercises()->first();

    Livewire::test('holocron.grind.plans.components.exercise', ['exercise' => $exerciseWithPivot])
        ->set('min_reps', 5);

    $pivot = $plan->exercises()->first()->pivot;
    expect($pivot->min_reps)->toBe(5);
});

it('updates pivot max_reps when property changes', function () {
    $plan = Plan::factory()->create();
    $exercise = Exercise::factory()->create();

    $plan->exercises()->attach($exercise, [
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    $exerciseWithPivot = $plan->exercises()->first();

    Livewire::test('holocron.grind.plans.components.exercise', ['exercise' => $exerciseWithPivot])
        ->set('max_reps', 15);

    $pivot = $plan->exercises()->first()->pivot;
    expect($pivot->max_reps)->toBe(15);
});

it('updates pivot order when property changes', function () {
    $plan = Plan::factory()->create();
    $exercise = Exercise::factory()->create();

    $plan->exercises()->attach($exercise, [
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    $exerciseWithPivot = $plan->exercises()->first();

    Livewire::test('holocron.grind.plans.components.exercise', ['exercise' => $exerciseWithPivot])
        ->set('order', 3);

    $pivot = $plan->exercises()->first()->pivot;
    expect($pivot->order)->toBe(3);
});
