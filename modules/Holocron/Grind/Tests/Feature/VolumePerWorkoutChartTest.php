<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Livewire\Components\VolumePerWorkoutChart;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Set;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\Grind\Models\WorkoutExercise;

it('calculates the trendline correctly', function () {
    $exercise = Exercise::factory()->create();

    $workout1 = Workout::factory()->create(['finished_at' => now()->subDays(2)]);
    $workoutExercise1 = WorkoutExercise::factory()->create([
        'workout_id' => $workout1->id,
        'exercise_id' => $exercise->id,
    ]);
    Set::factory()->create([
        'workout_exercise_id' => $workoutExercise1->id,
        'reps' => 10,
        'weight' => 100,
        'finished_at' => now()->subDays(2)->addMinute(),
    ]);

    $workout2 = Workout::factory()->create(['finished_at' => now()->subDay()]);
    $workoutExercise2 = WorkoutExercise::factory()->create([
        'workout_id' => $workout2->id,
        'exercise_id' => $exercise->id,
    ]);
    Set::factory()->create([
        'workout_exercise_id' => $workoutExercise2->id,
        'reps' => 10,
        'weight' => 110,
        'finished_at' => now()->subDay()->addMinute(),
    ]);

    $workout3 = Workout::factory()->create(['finished_at' => now()]);
    $workoutExercise3 = WorkoutExercise::factory()->create([
        'workout_id' => $workout3->id,
        'exercise_id' => $exercise->id,
    ]);
    Set::factory()->create([
        'workout_exercise_id' => $workoutExercise3->id,
        'reps' => 10,
        'weight' => 120,
        'finished_at' => now()->addMinute(),
    ]);

    Livewire::test(VolumePerWorkoutChart::class, ['exerciseId' => $exercise->id])
        ->assertViewHas('data', function (array $data) {
            $this->assertCount(3, $data);
            $this->assertEquals(1000, $data[0]['total_volume']);
            $this->assertEquals(1100, $data[1]['total_volume']);
            $this->assertEquals(1200, $data[2]['total_volume']);

            $this->assertEquals(1000, $data[0]['trendline']);
            $this->assertEquals(1100, $data[1]['trendline']);
            $this->assertEquals(1200, $data[2]['trendline']);

            return true;
        });
});

it('renders with no data', function () {
    $exercise = Exercise::factory()->create();

    Livewire::test(VolumePerWorkoutChart::class, ['exerciseId' => $exercise->id])
        ->assertSuccessful()
        ->assertViewHas('data', []);
});

it('handles a single data point with zero trendline', function () {
    $exercise = Exercise::factory()->create();

    $workout = Workout::factory()->create(['finished_at' => now()]);
    $workoutExercise = WorkoutExercise::factory()->create([
        'workout_id' => $workout->id,
        'exercise_id' => $exercise->id,
    ]);
    Set::factory()->create([
        'workout_exercise_id' => $workoutExercise->id,
        'reps' => 10,
        'weight' => 100,
        'finished_at' => now()->addMinute(),
    ]);

    Livewire::test(VolumePerWorkoutChart::class, ['exerciseId' => $exercise->id])
        ->assertViewHas('data', function (array $data) {
            $this->assertCount(1, $data);
            $this->assertEquals(1000, $data[0]['total_volume']);
            $this->assertEquals(0, $data[0]['trendline']);

            return true;
        });
});

it('formats dates as Y-m-d', function () {
    $exercise = Exercise::factory()->create();

    $workout1 = Workout::factory()->create(['finished_at' => '2025-03-15 10:00:00']);
    $we1 = WorkoutExercise::factory()->create([
        'workout_id' => $workout1->id,
        'exercise_id' => $exercise->id,
    ]);
    Set::factory()->create([
        'workout_exercise_id' => $we1->id,
        'reps' => 5,
        'weight' => 50,
        'finished_at' => '2025-03-15 10:05:00',
    ]);

    $workout2 = Workout::factory()->create(['finished_at' => '2025-03-16 10:00:00']);
    $we2 = WorkoutExercise::factory()->create([
        'workout_id' => $workout2->id,
        'exercise_id' => $exercise->id,
    ]);
    Set::factory()->create([
        'workout_exercise_id' => $we2->id,
        'reps' => 5,
        'weight' => 60,
        'finished_at' => '2025-03-16 10:05:00',
    ]);

    Livewire::test(VolumePerWorkoutChart::class, ['exerciseId' => $exercise->id])
        ->assertViewHas('data', function (array $data) {
            $this->assertEquals('2025-03-15', $data[0]['date']);
            $this->assertEquals('2025-03-16', $data[1]['date']);

            return true;
        });
});
