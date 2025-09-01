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
        ->assertViewHas('data', function ($data) {
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
