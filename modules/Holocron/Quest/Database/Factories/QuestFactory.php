<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Quest\Enums\QuestStatus;
use Modules\Holocron\Quest\Models\Quest;

/**
 * @extends Factory<Quest>
 */
class QuestFactory extends Factory
{
    protected $model = Quest::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence,
            'description' => fake()->paragraph,
            'status' => QuestStatus::InProgress,
            'images' => [],
        ];
    }
}
