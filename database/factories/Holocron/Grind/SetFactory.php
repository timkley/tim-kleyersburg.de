<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Grind;

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Set;
use App\Models\Holocron\Grind\Workout;
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
            'exercise_id' => Exercise::factory(),
            'workout_id' => Workout::factory(),
            'reps' => fake()->numberBetween(1, 10),
            'weight' => fake()->randomFloat(2, 50, 100),
        ];
    }
}
