<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Gear\Models\Category;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
