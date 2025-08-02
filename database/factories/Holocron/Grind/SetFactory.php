<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Grind;

use App\Models\Holocron\Grind\Set;
use App\Models\Holocron\Grind\WorkoutExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Set>
 */
class SetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_exercise_id' => WorkoutExercise::factory(),
            'reps' => fake()->numberBetween(1, 10),
            'weight' => fake()->randomFloat(2, 50, 100),
        ];
    }
}
