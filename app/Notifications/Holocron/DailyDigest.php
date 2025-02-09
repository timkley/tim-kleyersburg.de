<?php

declare(strict_types=1);

namespace App\Notifications\Holocron;

use App\Notifications\Chopper;
use App\Services\Nasa;
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
        $digest = $this->digest;
        $apod = collect(Nasa::apod())->only(['title', 'url'])->values()->implode(PHP_EOL);

        $information = implode(PHP_EOL, [$digest, $apod]);

        $answer = Chopper::conversation("Erstelle eine Tagesübersicht aus den folgenden Informationen: $information", 'daily-digest', now());

        return DiscordMessage::create($answer);
    }
}
