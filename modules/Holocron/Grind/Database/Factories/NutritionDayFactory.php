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
