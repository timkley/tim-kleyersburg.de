<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\Health;

use App\Models\Holocron\Health\DailyGoal;
use App\Notifications\Chopper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class GoalsNotReached extends Notification
{
    use Queueable;

    public function __construct(public Collection $missedGoals) {}

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        $missedGoals = $this->missedGoals->map(function (DailyGoal $goal) {
            $remaining = $goal->goal - $goal->amount;

            return "- {$goal->type->value}: $remaining {$goal->type->unit()->value} remaining";
        })->implode(PHP_EOL);

        $answer = Chopper::conversation("Create a notification for the not achieved goals: $missedGoals", 'missed-goals');

        return DiscordMessage::create($answer);
    }
}
