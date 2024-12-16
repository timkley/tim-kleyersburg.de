<?php

declare(strict_types=1);

namespace Database\Factories\Holocron;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holocron\Bookmark>
 */
class BookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => fake()->url(),
            'favicon' => 'asdf',
            'title' => fake()->sentence,
            'description' => fake()->sentence,
            'summary' => fake()->paragraph,
        ];
    }
}
