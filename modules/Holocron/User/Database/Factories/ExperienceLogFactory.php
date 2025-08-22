<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\User\Models\ExperienceLog;

/**
 * @extends Factory<ExperienceLog>
 */
class ExperienceLogFactory extends Factory
{
    protected $model = ExperienceLog::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->paragraph,
        ];
    }
}
