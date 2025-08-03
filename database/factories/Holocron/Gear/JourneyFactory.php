<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Gear;

use App\Models\Holocron\Gear\Journey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Journey>
 */
class JourneyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'destination' => $this->faker->city,
            'starts_at' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
            'ends_at' => $this->faker->dateTimeBetween('+2 weeks', '+3 weeks'),
            'participants' => ['adult'],
        ];
    }
}
