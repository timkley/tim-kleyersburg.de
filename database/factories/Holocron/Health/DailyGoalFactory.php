<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Health;

use App\Enums\Holocron\Health\GoalTypes;
use App\Enums\Holocron\Health\GoalUnits;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holocron\Health\DailyGoal>
 */
class DailyGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => GoalTypes::Water,
            'unit' => GoalUnits::Milliliters,
            'amount' => $this->faker->numberBetween(1, 100),
        ];
    }
}
