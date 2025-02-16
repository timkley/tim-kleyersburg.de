<?php

declare(strict_types=1);

namespace App\Notifications\Holocron;

use App\Notifications\Chopper;
use App\Services\Nasa;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class MorningDigest extends Notification
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
        $apod = Nasa::apod()->only(['title', 'url'])->values()->implode(PHP_EOL);

        $information = implode(PHP_EOL, [$digest, 'Nasa Bild des Tages: '.$apod]);

        $answer = Chopper::conversation(
            <<<EOT
Erstelle eine Tagesübersicht aus den folgenden Informationen
Achte darauf Kalendereinträge und Erinnerungen korrekt zu clustern:

$information
EOT,
            'digest',
            CarbonImmutable::now()->endOfDay()
        );

        return DiscordMessage::create($answer);
    }
}
