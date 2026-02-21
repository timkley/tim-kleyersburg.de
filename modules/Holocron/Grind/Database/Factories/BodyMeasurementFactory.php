<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\BodyMeasurement;

/**
 * @extends Factory<BodyMeasurement>
 */
class BodyMeasurementFactory extends Factory
{
    protected $model = BodyMeasurement::class;

    public function definition(): array
    {
        return [
            'date' => fake()->unique()->date(),
            'weight' => fake()->randomFloat(2, 70, 85),
            'body_fat' => fake()->randomFloat(1, 15, 25),
            'muscle_mass' => fake()->randomFloat(1, 50, 60),
            'visceral_fat' => fake()->numberBetween(4, 10),
            'bmi' => fake()->randomFloat(1, 20, 28),
            'body_water' => fake()->randomFloat(1, 50, 65),
        ];
    }
}
