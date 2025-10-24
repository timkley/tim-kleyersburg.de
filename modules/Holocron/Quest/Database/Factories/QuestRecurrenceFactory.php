<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

class QuestRecurrenceFactory extends Factory
{
    protected $model = QuestRecurrence::class;

    public function definition(): array
    {
        return [
            'quest_id' => Quest::factory(),
            'every_x_days' => 1,
            'recurrence_type' => QuestRecurrence::TYPE_RECURRENCE_BASED,
            'last_recurred_at' => $this->faker->dateTime(),
            'ends_at' => null,
        ];
    }

    public function completionBased(): static
    {
        return $this->state([
            'recurrence_type' => QuestRecurrence::TYPE_COMPLETION_BASED,
        ]);
    }

    public function everyDays(int $days): static
    {
        return $this->state([
            'every_x_days' => $days,
        ]);
    }
}
