<?php

declare(strict_types=1);

namespace Database\Factories\Holocron\Quest;

use App\Enums\Holocron\ReminderType;
use App\Models\Holocron\Quest\Quest;
use App\Models\Holocron\Quest\Reminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Reminder>
     */
    protected $model = Reminder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
