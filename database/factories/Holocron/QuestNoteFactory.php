<?php

declare(strict_types=1);

namespace Database\Factories\Holocron;

use App\Models\Holocron\Quest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holocron\QuestNote>
 */
class QuestNoteFactory extends Factory
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
