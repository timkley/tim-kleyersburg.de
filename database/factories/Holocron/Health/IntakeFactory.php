<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Health;

use App\Enums\Holocron\Health\IntakeTypes;
use App\Enums\Holocron\Health\IntakeUnits;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holocron\Health\Intake>
 */
class IntakeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => IntakeTypes::Water,
            'amount' => $this->faker->numberBetween(1, 100),
            'unit' => IntakeUnits::Milliliters,
        ];
    }
}
