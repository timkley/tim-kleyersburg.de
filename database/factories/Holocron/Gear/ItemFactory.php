<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Gear;

use App\Models\Holocron\Gear\Category;
use App\Models\Holocron\Gear\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
