<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\NutritionDay;

/**
 * @extends Factory<NutritionDay>
 */
class NutritionDayFactory extends Factory
{
    protected $model = NutritionDay::class;

    public function definition(): array
    {
        return [
            'date' => fake()->unique()->date(),
            'type' => fake()->randomElement(['training', 'rest', 'sick']),
            'training_label' => null,
            'meals' => [
                ['name' => 'Frühstück', 'time' => '08:00', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
                ['name' => 'Mittagessen', 'time' => '12:30', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70],
            ],
            'total_kcal' => 1200,
            'total_protein' => 70,
            'total_fat' => 45,
            'total_carbs' => 120,
        ];
    }

    public function training(string $label = 'upper'): static
    {
        return $this->state(fn () => [
            'type' => 'training',
            'training_label' => $label,
        ]);
    }

    public function rest(): static
    {
        return $this->state(fn () => [
            'type' => 'rest',
            'training_label' => null,
        ]);
    }
}
