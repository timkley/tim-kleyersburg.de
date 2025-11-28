<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.grind.exercises.index'))
        ->assertRedirect();
});

it('works', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.grind.exercises.index')
        ->assertSuccessful();
});

it('can create exercises', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.grind.exercises.create-modal')
        ->set('form.name', 'Bench Press')
        ->set('form.description', 'A great chest exercise')
        ->set('form.instructions', 'Lower the bar to your chest')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Exercise::count())->toBe(1);
    expect(Exercise::first()->name)->toBe('Bench Press');
    expect(Exercise::first()->description)->toBe('A great chest exercise');
    expect(Exercise::first()->instructions)->toBe('Lower the bar to your chest');
});

it('validates exercise name is required', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.grind.exercises.create-modal')
        ->set('form.name', '')
        ->call('submit')
        ->assertHasErrors(['form.name' => 'required']);
});

it('can delete exercises', function () {
    $user = User::factory()->create();
    actingAs($user);

    Exercise::factory()->create();

    Livewire::test('holocron.grind.exercises.index')
        ->call('delete', 1);

    expect(Exercise::count())->toBe(0);
});

it('shows an exercise', function () {
    $user = User::factory()->create();
    actingAs($user);

    $exercise = Exercise::factory()->create();
    $workout = Modules\Holocron\Grind\Models\Workout::factory()->create([
        'finished_at' => now(),
    ]);
    $workoutExercise = $workout->exercises()->create([
        'exercise_id' => $exercise->id,
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);
    Modules\Holocron\Grind\Models\Set::factory()->create([
        'workout_exercise_id' => $workoutExercise->id,
    ]);

    get(route('holocron.grind.exercises.show', $exercise))
        ->assertOk();
});

it('can edit exercise name', function () {
    $user = User::factory()->create();
    actingAs($user);

    $exercise = Exercise::factory()->create([
        'name' => 'Old Name',
    ]);

    Livewire::test('holocron.grind.exercises.show', ['exercise' => $exercise])
        ->set('form.name', 'New Name')
        ->assertHasNoErrors();

    expect($exercise->fresh()->name)->toBe('New Name');
});

it('can edit exercise description', function () {
    $user = User::factory()->create();
    actingAs($user);

    $exercise = Exercise::factory()->create([
        'description' => 'Old Description',
    ]);

    Livewire::test('holocron.grind.exercises.show', ['exercise' => $exercise])
        ->set('form.description', 'New Description')
        ->assertHasNoErrors();

    expect($exercise->fresh()->description)->toBe('New Description');
});

it('can edit exercise instructions', function () {
    $user = User::factory()->create();
    actingAs($user);

    $exercise = Exercise::factory()->create([
        'instructions' => 'Old Instructions',
    ]);

    Livewire::test('holocron.grind.exercises.show', ['exercise' => $exercise])
        ->set('form.instructions', 'New Instructions')
        ->assertHasNoErrors();

    expect($exercise->fresh()->instructions)->toBe('New Instructions');
});
