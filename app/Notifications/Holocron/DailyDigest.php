<?php

declare(strict_types=1);

namespace App\Notifications\Holocron;

use App\Notifications\Chopper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class DailyDigest extends Notification
{
    use Queueable;

    public function __construct(public string $digest) {}

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        $answer = Chopper::conversation("Create a digest from these information: $this->digest", 'daily-digest', now());

        return DiscordMessage::create($answer);
    }
}
