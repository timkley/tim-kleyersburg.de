<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Quest;

use App\Models\Holocron\Quest\Note;
use App\Models\Holocron\Quest\Quest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quest_id' => Quest::factory(),
            'content' => fake()->paragraph,
            'status' => null,
        ];
    }
}
