<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Gear\Models\Category;
use Modules\Holocron\Gear\Models\Item;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->word,
            'quantity_per_day' => $this->faker->randomFloat(2, 0, 5),
            'quantity' => $this->faker->numberBetween(1, 10),
            'properties' => [],
        ];
    }
}
