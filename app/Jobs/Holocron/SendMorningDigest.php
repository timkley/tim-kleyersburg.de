<?php

declare(strict_types=1);

namespace App\Jobs\Holocron;

use App\Notifications\Chopper;
use App\Services\Nasa;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use NotificationChannels\Discord\Discord;

class SendMorningDigest implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $digest = cache('daily-digest');

        if (! $digest) {
            return;
        }

        $apod = Nasa::apod()->only(['title', 'url'])->values()->implode(PHP_EOL);

        $information = implode(PHP_EOL, [$digest, 'Nasa Bild des Tages: '.$apod]);

        $answer = Chopper::conversation(
            <<<EOT
Erstelle eine Tagesübersicht aus den folgenden Informationen.
Clustere Kalendereinträge und Erinnerungen.
Antworte nur mit der Tagesübersicht, als ob du dich in einer Konversation befindest.

Informationen:
$information
EOT,
            'digest',
            CarbonImmutable::now()->endOfDay()
        );

        /** @var Discord $discord */
        $discord = app(Discord::class);
        $discord->send(config('services.discord.tim_channel'), [
            'content' => $answer,
        ]);
    }
}
