<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holocron\School\VocabularyWord>
 */
class VocabularyWordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'english' => $this->faker->word,
            'german' => $this->faker->word,
            'right' => 0,
            'wrong' => 0,
        ];
    }
}
