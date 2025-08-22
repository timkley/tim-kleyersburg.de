<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Workout;

/**
 * @extends Factory<Workout>
 */
class WorkoutFactory extends Factory
{
    protected $model = Workout::class;

    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'started_at' => fake()->dateTimeBetween('-1 week'),
        ];
    }
}
