<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Quest\Enums\QuestRecurrenceType;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

class QuestRecurrenceFactory extends Factory
{
    protected $model = QuestRecurrence::class;

    public function definition(): array
    {
        return [
            'quest_id' => Quest::factory(),
            'type' => $this->faker->randomElement(QuestRecurrenceType::cases()),
            'value' => $this->faker->numberBetween(1, 30),
            'last_recurred_at' => $this->faker->dateTime(),
            'ends_at' => null,
        ];
    }
}
