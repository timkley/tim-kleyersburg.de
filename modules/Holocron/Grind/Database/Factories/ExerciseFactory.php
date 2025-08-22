<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Grind\Models\Exercise;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'description' => fake()->paragraph,
            'instructions' => fake()->paragraph,
        ];
    }
}
