<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Quest;

use App\Enums\Holocron\QuestStatus;
use App\Models\Holocron\Quest\Quest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quest>
 */
class QuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence,
            'description' => fake()->paragraph,
            'status' => QuestStatus::InProgress,
        ];
    }
}
