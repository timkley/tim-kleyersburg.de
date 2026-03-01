<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Set;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\Grind\Models\WorkoutExercise;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());

    $exercise = Exercise::factory()->create();
    $plan = Plan::factory()->create();
    $workout = Workout::factory()->create(['plan_id' => $plan->id]);

    $this->workoutExercise = WorkoutExercise::factory()->create([
        'workout_id' => $workout->id,
        'exercise_id' => $exercise->id,
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);

    $this->set = Set::factory()->create([
        'workout_exercise_id' => $this->workoutExercise->id,
        'weight' => 80.0,
        'reps' => 10,
    ]);
});

it('renders the set component', function () {
    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 1,
    ])
        ->assertSuccessful()
        ->assertSet('weight', 80.0)
        ->assertSet('reps', 10);
});

it('mounts with set weight and reps', function () {
    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 1,
    ])
        ->assertSet('weight', 80.0)
        ->assertSet('reps', 10)
        ->assertSet('minReps', 8)
        ->assertSet('maxReps', 12)
        ->assertSet('iteration', 1);
});

it('updates weight when property changes', function () {
    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 1,
    ])
        ->set('weight', 85.0)
        ->assertHasNoErrors();

    expect($this->set->fresh()->weight)->toBe(85.0);
});

it('updates reps when property changes', function () {
    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 1,
    ])
        ->set('reps', 12)
        ->assertHasNoErrors();

    expect($this->set->fresh()->reps)->toBe(12);
});

it('can start a set', function () {
    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 1,
    ])
        ->call('start')
        ->assertDispatched('set:started');

    expect($this->set->fresh()->started_at)->not->toBeNull();
});

it('finishes sibling sets when starting a new one', function () {
    $siblingSet = Set::factory()->create([
        'workout_exercise_id' => $this->workoutExercise->id,
        'weight' => 75.0,
        'reps' => 8,
        'started_at' => now()->subMinutes(5),
    ]);

    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 2,
    ])
        ->call('start');

    expect($siblingSet->fresh()->finished_at)->not->toBeNull();
    expect($this->set->fresh()->started_at)->not->toBeNull();
});

it('can finish a set', function () {
    $this->set->update(['started_at' => now()->subMinute()]);

    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 1,
    ])
        ->call('finish')
        ->assertDispatched('set:finished');

    expect($this->set->fresh()->finished_at)->not->toBeNull();
});

it('validates weight is required', function () {
    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 1,
    ])
        ->set('weight', '')
        ->assertHasErrors(['weight' => 'required']);
});

it('validates reps is required', function () {
    Livewire::test('holocron.grind.workouts.components.set', [
        'set' => $this->set,
        'minReps' => 8,
        'maxReps' => 12,
        'iteration' => 1,
    ])
        ->set('reps', '')
        ->assertHasErrors(['reps' => 'required']);
});
