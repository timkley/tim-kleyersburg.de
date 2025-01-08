<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\Health;

use Denk\Facades\Denk;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class InsufficientCreatineIntake extends Notification
{
    use Queueable;

    public function __construct()
    {
    }

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
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
            ->prompt('Tim hat heute noch kein Kreatin eingenommen.')
            ->generate();

        return DiscordMessage::create($text);
    }
}
