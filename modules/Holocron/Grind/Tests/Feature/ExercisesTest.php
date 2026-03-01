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

it('validates exercise name minimum length', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.grind.exercises.create-modal')
        ->set('form.name', 'ab')
        ->call('submit')
        ->assertHasErrors(['form.name' => 'min']);
});

it('validates exercise name when editing', function () {
    $user = User::factory()->create();
    actingAs($user);

    $exercise = Exercise::factory()->create(['name' => 'Bench Press']);

    Livewire::test('holocron.grind.exercises.show', ['exercise' => $exercise])
        ->set('form.name', 'ab')
        ->assertHasErrors(['form.name' => 'min']);

    expect($exercise->fresh()->name)->toBe('Bench Press');
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

it('ignores updates for non-form properties on show', function () {
    $user = User::factory()->create();
    actingAs($user);

    $exercise = Exercise::factory()->create(['name' => 'Bench Press']);

    Livewire::test('holocron.grind.exercises.show', ['exercise' => $exercise])
        ->set('exercise', $exercise)
        ->assertHasNoErrors();

    expect($exercise->fresh()->name)->toBe('Bench Press');
});

it('ignores updates for unknown form properties on show', function () {
    $user = User::factory()->create();
    actingAs($user);

    $exercise = Exercise::factory()->create(['name' => 'Bench Press']);

    // Setting form.exercise triggers updated() but formProperty 'exercise'
    // is not in the allowed list, so it should return early without saving
    Livewire::test('holocron.grind.exercises.show', ['exercise' => $exercise])
        ->set('form.exercise', null)
        ->assertHasNoErrors();

    expect($exercise->fresh()->name)->toBe('Bench Press');
});

it('lists all exercises on index', function () {
    $user = User::factory()->create();
    actingAs($user);

    Exercise::factory()->create(['name' => 'Bench Press']);
    Exercise::factory()->create(['name' => 'Squat']);

    Livewire::test('holocron.grind.exercises.index')
        ->assertSee('Bench Press')
        ->assertSee('Squat');
});

it('dispatches exercise-created event after submission', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.grind.exercises.create-modal')
        ->set('form.name', 'Deadlift')
        ->call('submit')
        ->assertDispatched('exercise-created');
});

it('resets form after successful creation', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.grind.exercises.create-modal')
        ->set('form.name', 'Bench Press')
        ->set('form.description', 'Test')
        ->call('submit')
        ->assertSet('form.name', '')
        ->assertSet('form.description', null);
});

it('renders show with volume data', function () {
    $user = User::factory()->create();
    actingAs($user);

    $exercise = Exercise::factory()->create();

    Livewire::test('holocron.grind.exercises.show', ['exercise' => $exercise])
        ->assertSuccessful()
        ->assertViewHas('data');
});

it('validates exercise name max length on creation', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.grind.exercises.create-modal')
        ->set('form.name', str_repeat('a', 256))
        ->call('submit')
        ->assertHasErrors(['form.name' => 'max']);
});
