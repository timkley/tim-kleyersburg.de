<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExperienceLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExperienceLog>
 */
class ExperienceLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => $this->faker->numberBetween(1, 100),
            'description' => $this->faker->paragraph,
        ];
    }
}
