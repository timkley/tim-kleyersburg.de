<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Holocron\QuestStatus;
use App\Models\Holocron\Quest\Reminder;
use App\Notifications\DiscordTimChannel;
use App\Notifications\Holocron\QuestReminder;
use Illuminate\Console\Command;

class ProcessReminders extends Command
{
    /** @var string */
    protected $signature = 'reminders:process';

    /** @var string */
    protected $description = 'Process due reminders and send notifications';

    public function handle(): int
    {
        $dueReminders = Reminder::query()
            ->due()
            ->whereHas('quest', function ($query) {
                $query->where('status', '!=', QuestStatus::Complete->value);
            })
            ->with('quest')
            ->get();

        if ($dueReminders->isEmpty()) {
            $this->info('No due reminders found.');

            return 0;
        }

        $this->info("Processing {$dueReminders->count()} due reminders...");

        foreach ($dueReminders as $reminder) {
            $this->processReminder($reminder);
        }

        $this->info('All due reminders processed successfully.');

        return 0;
    }

    protected function processReminder(Reminder $reminder): void
    {
        $this->info("Processing reminder #{$reminder->id} for quest '{$reminder->quest->name}'");

        // Send notification
        $this->sendNotification($reminder);

        // Mark as processed (this will also schedule the next occurrence for recurring reminders)
        $reminder->markAsProcessed();

        $this->info("Reminder #{$reminder->id} processed successfully.");
    }

    protected function sendNotification(Reminder $reminder): void
    {
        (new DiscordTimChannel)->notify(new QuestReminder($reminder));

        $this->info("Notification sent for reminder #{$reminder->id}");
    }
}
