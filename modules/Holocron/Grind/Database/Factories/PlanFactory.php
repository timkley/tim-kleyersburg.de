<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\Plan;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'description' => fake()->paragraph,
        ];
    }
}
