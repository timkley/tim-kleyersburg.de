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

    $this->workout = Workout::factory()->create(['plan_id' => $plan->id]);

    $this->workoutExercise = WorkoutExercise::factory()->create([
        'workout_id' => $this->workout->id,
        'exercise_id' => $exercise->id,
        'sets' => 3,
        'min_reps' => 8,
        'max_reps' => 12,
        'order' => 1,
    ]);
});

it('renders the timer component', function () {
    Livewire::test('holocron.grind.workouts.components.timer', ['workout' => $this->workout])
        ->assertSuccessful();
});

it('shows no timer when no sets are active or finished', function () {
    Livewire::test('holocron.grind.workouts.components.timer', ['workout' => $this->workout])
        ->assertViewHas('lastFinishedSet', null);
});

it('shows no last finished set when a set is in progress', function () {
    Set::factory()->create([
        'workout_exercise_id' => $this->workoutExercise->id,
        'started_at' => now()->subMinute(),
        'finished_at' => null,
    ]);

    Livewire::test('holocron.grind.workouts.components.timer', ['workout' => $this->workout])
        ->assertViewHas('lastFinishedSet', null);
});

it('shows the last finished set for rest timer', function () {
    $finishedSet = Set::factory()->create([
        'workout_exercise_id' => $this->workoutExercise->id,
        'started_at' => now()->subMinutes(2),
        'finished_at' => now()->subMinute(),
    ]);

    $component = Livewire::test('holocron.grind.workouts.components.timer', ['workout' => $this->workout]);

    $lastFinishedSet = $component->viewData('lastFinishedSet');

    expect($lastFinishedSet)->not->toBeNull()
        ->and($lastFinishedSet->id)->toBe($finishedSet->id);
});

it('shows the most recently updated finished set', function () {
    $olderSet = Set::factory()->create([
        'workout_exercise_id' => $this->workoutExercise->id,
        'started_at' => now()->subMinutes(10),
        'finished_at' => now()->subMinutes(8),
        'updated_at' => now()->subMinutes(8),
    ]);

    $latestFinished = Set::factory()->create([
        'workout_exercise_id' => $this->workoutExercise->id,
        'started_at' => now()->subMinutes(5),
        'finished_at' => now()->subMinute(),
        'updated_at' => now()->subMinute(),
    ]);

    $component = Livewire::test('holocron.grind.workouts.components.timer', ['workout' => $this->workout]);

    $lastFinishedSet = $component->viewData('lastFinishedSet');

    expect($lastFinishedSet)->not->toBeNull()
        ->and($lastFinishedSet->id)->toBe($latestFinished->id);
});
