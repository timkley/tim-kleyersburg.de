<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Gear;

use App\Models\Holocron\Gear\Item;
use App\Models\Holocron\Gear\Journey;
use App\Models\Holocron\Gear\JourneyItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JourneyItem>
 */
class JourneyItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'journey_id' => Journey::factory(),
            'item_id' => Item::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'packed_for_departure' => $this->faker->boolean,
            'packed_for_return' => $this->faker->boolean,
        ];
    }
}
