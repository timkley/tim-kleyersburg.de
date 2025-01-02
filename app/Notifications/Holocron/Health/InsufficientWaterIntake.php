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
            ->prompt('Erstelle einen kurzen Text um Tim daran zu erinnern, genug Wasser zu trinken. Er muss heute noch '.$remainingInLiters.' Liter trinken.')
            ->generate();

        return DiscordMessage::create($text);
    }
}
