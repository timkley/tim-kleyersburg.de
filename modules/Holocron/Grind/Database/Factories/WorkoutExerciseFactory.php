<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Workout;
use Modules\Holocron\Grind\Models\WorkoutExercise;

/**
 * @extends Factory<WorkoutExercise>
 */
class WorkoutExerciseFactory extends Factory
{
    protected $model = WorkoutExercise::class;

    public function definition(): array
    {
        return [
            'workout_id' => Workout::factory(),
            'exercise_id' => Exercise::factory(),
            'sets' => fake()->numberBetween(1, 5),
            'min_reps' => fake()->numberBetween(1, 8),
            'max_reps' => fake()->numberBetween(8, 15),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
