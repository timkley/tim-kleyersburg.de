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
