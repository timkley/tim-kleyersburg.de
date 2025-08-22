<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Gear\Models\Item;
use Modules\Holocron\Gear\Models\Journey;
use Modules\Holocron\Gear\Models\JourneyItem;

/**
 * @extends Factory<JourneyItem>
 */
class JourneyItemFactory extends Factory
{
    protected $model = JourneyItem::class;

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
