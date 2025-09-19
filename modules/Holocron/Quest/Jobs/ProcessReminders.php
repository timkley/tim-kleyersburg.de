<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Jobs;

use App\Notifications\DiscordTimChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Holocron\Quest\Models\Reminder;
use Modules\Holocron\User\Notifications\QuestReminder;

class ProcessReminders implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $dueReminders = Reminder::query()
            ->due()
            ->whereHas('quest', function ($query) {
                $query->whereNull('completed_at');
            })
            ->with('quest')
            ->get();

        if ($dueReminders->isEmpty()) {
            return;
        }

        foreach ($dueReminders as $reminder) {
            $this->processReminder($reminder);
        }
    }

    protected function processReminder(Reminder $reminder): void
    {
        (new DiscordTimChannel)->notify(new QuestReminder($reminder));

        $reminder->markAsProcessed();
    }
}
