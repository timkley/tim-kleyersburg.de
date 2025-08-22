<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Enums\GoalUnit;
use Modules\Holocron\User\Models\DailyGoal;

/**
 * @extends Factory<DailyGoal>
 */
class DailyGoalFactory extends Factory
{
    protected $model = DailyGoal::class;

    public function definition(): array
    {
        return [
            'type' => GoalType::Water,
            'unit' => GoalUnit::Milliliters,
            'amount' => $this->faker->numberBetween(1, 100),
        ];
    }
}
