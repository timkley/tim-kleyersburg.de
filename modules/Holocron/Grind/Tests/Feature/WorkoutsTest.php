<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Set;
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

it('can finish a workout without experience logs table', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise,
        [
            'sets' => 3,
            'min_reps' => 3,
            'max_reps' => 3,
            'order' => 1,
        ]
    )->create();

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id)
        ->assertRedirect(route('holocron.grind.workouts.show', [1]));

    Schema::dropIfExists('experience_logs');

    $workout = Workout::query()->firstOrFail();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->call('finish');

    expect($workout->fresh()->finished_at)->not()->toBeNull();
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

it('prepopulates sets from latest performance regardless of which plan the exercise came from', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $sharedExercise = Exercise::factory()->create();

    $planA = Plan::factory()->hasAttached(
        $sharedExercise,
        [
            'sets' => 2,
            'min_reps' => 5,
            'max_reps' => 10,
            'order' => 1,
        ]
    )->create();

    $planB = Plan::factory()->hasAttached(
        $sharedExercise,
        [
            'sets' => 2,
            'min_reps' => 5,
            'max_reps' => 10,
            'order' => 1,
        ]
    )->create();

    // Start and finish a workout with Plan A
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $planA->id);

    $workoutA = Workout::first();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workoutA])
        ->set('weight', 80)
        ->set('reps', 8)
        ->call('recordSet')
        ->set('weight', 85)
        ->set('reps', 6)
        ->call('recordSet')
        ->call('finish');

    // Start a workout with Plan B — the shared exercise should get prepopulated from Plan A's workout
    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $planB->id);

    $workoutB = Workout::query()->latest('id')->first();

    expect($workoutB->sets->count())->toBe(2);

    $weights = $workoutB->sets->pluck('weight')->sort()->values();
    $reps = $workoutB->sets->pluck('reps')->sort()->values();

    expect($weights->toArray())->toBe([80.0, 85.0]);
    expect($reps->toArray())->toBe([6, 8]);
});

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

it('sets nutrition day to training when starting a workout', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $plan = Plan::factory()->hasAttached(
        Exercise::factory(),
        [
            'sets' => 3,
            'min_reps' => 5,
            'max_reps' => 10,
            'order' => 1,
        ]
    )->create(['name' => 'Upper']);

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id);

    $day = NutritionDay::query()->whereDate('date', today())->first();

    expect($day)->not->toBeNull()
        ->and($day->type)->toBe('training')
        ->and($day->training_label)->toBe('Upper');
});

it('can set the current exercise on a workout', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise1 = Exercise::factory()->create();
    $exercise2 = Exercise::factory()->create();

    $plan = Plan::factory()->create();
    $plan->exercises()->attach($exercise1, ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 1]);
    $plan->exercises()->attach($exercise2, ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 2]);

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id);

    $workout = Workout::query()->firstOrFail();
    $workoutExercise2 = $workout->exercises()->where('exercise_id', $exercise2->id)->first();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->call('setExercise', $workoutExercise2->id);

    expect($workout->fresh()->current_exercise_id)->toBe($workoutExercise2->id);
});

it('can swap an exercise in a workout', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise1 = Exercise::factory()->create();
    $exercise2 = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise1,
        ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 1]
    )->create();

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id);

    $workout = Workout::query()->firstOrFail();
    $workoutExercise = $workout->exercises()->first();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->set('exerciseIdToChange', $workoutExercise->id)
        ->call('swapExercise', $exercise2->id);

    expect($workoutExercise->fresh()->exercise_id)->toBe($exercise2->id);
});

it('can delete an exercise from a workout', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise1 = Exercise::factory()->create();
    $exercise2 = Exercise::factory()->create();

    $plan = Plan::factory()->create();
    $plan->exercises()->attach($exercise1, ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 1]);
    $plan->exercises()->attach($exercise2, ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 2]);

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id);

    $workout = Workout::query()->firstOrFail();

    expect($workout->exercises)->toHaveCount(2);

    $workoutExercise = $workout->exercises()->first();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->set('exerciseIdToChange', $workoutExercise->id)
        ->call('deleteExercise');

    expect($workout->fresh()->exercises)->toHaveCount(1);
});

it('can record a set on a workout', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise,
        ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 1]
    )->create();

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id);

    $workout = Workout::query()->firstOrFail();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->set('weight', 100.0)
        ->set('reps', 10)
        ->call('recordSet')
        ->assertHasNoErrors();

    expect($workout->sets)->toHaveCount(1);

    $set = $workout->sets()->first();
    expect($set->weight)->toBe(100.0)
        ->and($set->reps)->toBe(10);
});

it('validates weight and reps when recording a set', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise,
        ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 1]
    )->create();

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id);

    $workout = Workout::query()->firstOrFail();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->set('weight', '')
        ->set('reps', '')
        ->call('recordSet')
        ->assertHasErrors(['weight', 'reps']);
});

it('dispatches workout:finished event when finishing', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise,
        ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 1]
    )->create();

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id);

    $workout = Workout::query()->firstOrFail();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->call('finish')
        ->assertDispatched('workout:finished');
});

it('renders workout show page with exercises and available exercises', function () {
    actingAs(User::factory()->create(['email' => 'timkley@gmail.com']));

    $exercise = Exercise::factory()->create();

    $plan = Plan::factory()->hasAttached(
        $exercise,
        ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 1]
    )->create();

    Livewire::test('holocron.grind.workouts.index')
        ->call('start', $plan->id);

    $workout = Workout::query()->firstOrFail();

    Livewire::test('holocron.grind.workouts.show', ['workout' => $workout])
        ->assertSuccessful()
        ->assertViewHas('workoutExercises')
        ->assertViewHas('currentExercise')
        ->assertViewHas('availableExercises');
});

it('shows unfinished workouts on index', function () {
    $plan = Plan::factory()->hasAttached(
        Exercise::factory(),
        ['sets' => 3, 'min_reps' => 8, 'max_reps' => 12, 'order' => 1]
    )->create();

    Workout::factory()->create([
        'plan_id' => $plan->id,
        'finished_at' => null,
    ]);

    Livewire::test('holocron.grind.workouts.index')
        ->assertSuccessful()
        ->assertViewHas('unfinishedWorkouts');
});
