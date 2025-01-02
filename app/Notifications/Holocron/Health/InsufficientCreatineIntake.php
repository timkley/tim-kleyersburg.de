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
            ->prompt('Erstelle einen kurzen Text um Tim daran zu erinnern, sein Kreatin zu nehmen, damit er kein Lauch bleibt.')
            ->generate();

        return DiscordMessage::create($text);
    }
}
