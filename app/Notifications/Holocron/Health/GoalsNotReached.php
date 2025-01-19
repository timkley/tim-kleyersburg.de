<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\Health;

use Denk\Facades\Denk;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class GoalsNotReached extends Notification
{
    use Queueable;

    public function __construct(public Collection $missedGoals)
    {
    }

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        $missedGoals = $this->missedGoals->map(function ($goal) {
            return "- {$goal->type->value} - achieved: {$goal->amount} - goal: {$goal->goal}";
        })->implode(PHP_EOL);

        $text = Denk::text()
            ->systemPrompt(
                <<<'EOT'
Your job is to create notifications.

- answer in german
- make sure german grammar and dictation is correct, don't answer before you are sure it is correct
- direct the message to the user
- be concise
- no emojis
- you can be humorous

Users name: Tim
EOT
            )
            ->prompt("Create a notification for the not achieved goals: $missedGoals")
            ->generate();

        return DiscordMessage::create($text);
    }
}
