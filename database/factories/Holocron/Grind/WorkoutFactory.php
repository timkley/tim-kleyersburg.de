<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Grind;

use App\Models\Holocron\Grind\Plan;
use App\Models\Holocron\Grind\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workout>
 */
class WorkoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'started_at' => fake()->dateTimeBetween('-1 week'),
        ];
    }
}
