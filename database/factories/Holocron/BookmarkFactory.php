<?php

declare(strict_types=1);

namespace Database\Factories\Holocron;

use App\Models\Webpage;
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
            'webpage_id' => Webpage::factory()
        ];
    }
}
