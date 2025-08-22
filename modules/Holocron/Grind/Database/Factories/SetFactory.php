<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\Set;
use Modules\Holocron\Grind\Models\WorkoutExercise;

/**
 * @extends Factory<Set>
 */
class SetFactory extends Factory
{
    protected $model = Set::class;

    public function definition(): array
    {
        return [
            'workout_exercise_id' => WorkoutExercise::factory(),
            'reps' => fake()->numberBetween(1, 10),
            'weight' => fake()->randomFloat(2, 50, 100),
        ];
    }
}
