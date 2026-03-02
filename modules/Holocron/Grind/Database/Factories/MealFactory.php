<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
{
    protected $model = Meal::class;

    public function definition(): array
    {
        return [
            'nutrition_day_id' => NutritionDay::factory(),
            'name' => fake()->randomElement(['Frühstück', 'Mittagessen', 'Abendessen', 'Snack', 'Protein Shake']),
            'time' => fake()->optional()->time('H:i'),
            'kcal' => fake()->numberBetween(100, 900),
            'protein' => fake()->numberBetween(5, 60),
            'fat' => fake()->numberBetween(2, 40),
            'carbs' => fake()->numberBetween(10, 100),
        ];
    }
}
