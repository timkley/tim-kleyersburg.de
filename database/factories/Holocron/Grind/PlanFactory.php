<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Grind;

use App\Models\Holocron\Grind\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'description' => fake()->paragraph,
        ];
    }
}
