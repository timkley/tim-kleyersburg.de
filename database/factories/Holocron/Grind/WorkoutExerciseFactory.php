<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Grind;

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holocron\Grind\WorkoutExercise>
 */
class WorkoutExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
