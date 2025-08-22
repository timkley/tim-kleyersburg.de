<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Holocron\Quest\Enums\ReminderType;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function definition(): array
    {
        return [
            'quest_id' => Quest::factory(),
            'remind_at' => $this->faker->dateTimeBetween('now', '+1 week'),
            'type' => ReminderType::Once,
            'recurrence_pattern' => null,
            'last_processed_at' => null,
        ];
    }

    /**
     * Configure the model factory to create a recurring reminder.
     */
    public function recurring(): self
    {
        return $this->state(function () {
            return [
                'type' => ReminderType::Cron,
                'recurrence_pattern' => '0 9 * * *', // Every day at 9 AM
            ];
        });
    }

    /**
     * Configure the model factory to create a one-time reminder.
     */
    public function once(): self
    {
        return $this->state(function () {
            return [
                'type' => ReminderType::Once,
                'recurrence_pattern' => null,
            ];
        });
    }

    /**
     * Configure the model factory to create a cron reminder.
     */
    public function cron(): self
    {
        return $this->state(function () {
            return [
                'type' => ReminderType::Cron,
                'recurrence_pattern' => '0 9 * * *', // Every day at 9 AM
            ];
        });
    }

    /**
     * Configure the model factory to create a processed reminder.
     */
    public function processed(): self
    {
        return $this->state(function () {
            return [
                'last_processed_at' => now(),
            ];
        });
    }
}
