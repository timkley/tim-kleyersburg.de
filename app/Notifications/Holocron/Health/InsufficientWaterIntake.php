<?php

declare(strict_types=1);

namespace App\Notifications\Holocron\Health;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class InsufficientWaterIntake extends Notification
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
        return DiscordMessage::create('Du hast heute noch nicht genug Wasser getrunken. Trink doch bitte etwas.');
    }
}
