<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\Health;

use Denk\Facades\Denk;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class InsufficientWaterIntake extends Notification
{
    use Queueable;

    public function __construct(public int $remaining)
    {
    }

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        $remainingInLiters = round($this->remaining / 1000, 1);

        $text = Denk::text()
            ->temperature(1.1)
            ->systemPrompt(<<<EOT
Your job is to create notifications.

- answer in german
- make sure german grammar and dictation is correct, don't answer before you are sure it is correct
- direct the message to the user
- be consice
- no emojis
- be funny
EOT
            )
            ->prompt('Tim muss heute noch '.$remainingInLiters.' Liter Wasser trinken.')
            ->generate();

        return DiscordMessage::create($text);
    }
}
