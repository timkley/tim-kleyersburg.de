<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.grind.plans.index'))
        ->assertRedirect();
});

it('works', function () {
    Livewire::test('holocron.grind.plans.index')
        ->assertSuccessful();
});

it('can add a plan', function () {
    Livewire::test('holocron.grind.plans.index')
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

    Livewire::test('holocron.grind.plans.index')
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

it('can remove an exercise from a plan', function () {
    $plan = Plan::factory()->create();
    $exercise = Exercise::factory()->create();

    $plan->exercises()->attach($exercise, [
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    expect($plan->exercises)->toHaveCount(1);

    Livewire::test('holocron.grind.plans.show', [$plan->id])
        ->call('removeExercise', $exercise->id);

    expect($plan->fresh()->exercises)->toHaveCount(0);
});

it('validates plan name is required', function () {
    Livewire::test('holocron.grind.plans.index')
        ->set('name', '')
        ->call('submit')
        ->assertHasErrors(['name' => 'required']);
});

it('validates plan name minimum length', function () {
    Livewire::test('holocron.grind.plans.index')
        ->set('name', 'ab')
        ->call('submit')
        ->assertHasErrors(['name' => 'min']);
});

it('validates plan name maximum length', function () {
    Livewire::test('holocron.grind.plans.index')
        ->set('name', str_repeat('a', 256))
        ->call('submit')
        ->assertHasErrors(['name' => 'max']);
});

it('resets name after creating a plan', function () {
    Livewire::test('holocron.grind.plans.index')
        ->set('name', 'My Plan')
        ->call('submit')
        ->assertSet('name', null);
});

it('lists all plans on index', function () {
    Plan::factory()->create(['name' => 'Upper Body']);
    Plan::factory()->create(['name' => 'Lower Body']);

    Livewire::test('holocron.grind.plans.index')
        ->assertSee('Upper Body')
        ->assertSee('Lower Body');
});

it('shows available exercises on plan show page', function () {
    $plan = Plan::factory()->create();
    Exercise::factory()->create(['name' => 'Bench Press']);

    Livewire::test('holocron.grind.plans.show', [$plan->id])
        ->assertViewHas('availableExercises');
});
