<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Health;

use App\Enums\Holocron\Health\GoalType;
use App\Enums\Holocron\Health\GoalUnit;
use App\Models\Holocron\Health\DailyGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyGoal>
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
            'type' => GoalType::Water,
            'unit' => GoalUnit::Milliliters,
            'amount' => $this->faker->numberBetween(1, 100),
        ];
    }
}
