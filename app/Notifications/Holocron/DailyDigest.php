<?php

declare(strict_types=1);

namespace App\Notifications\Holocron;

use App\Services\Weather;
use Denk\Facades\Denk;
use Denk\ValueObjects\DeveloperMessage;
use Denk\ValueObjects\UserMessage;
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
        $date = now()->toDateString();
        $time = now()->toTimeString();
        $forecast = Weather::today();
        $condition = $forecast->condition;
        $maxTemp = $forecast->maxTemp;
        $minTemp = $forecast->minTemp;

        $messages = [
            new DeveloperMessage(
                <<<EOT
Your job is to create a daily digest.
Today is the $date, it is currently $time, adjust the message accordingly.
The weather condition is "$condition", with a max temperature of $maxTemp and a min temperature of $minTemp.

- answer in german
- make sure german grammar and dictation is correct, don't answer before you are sure it is correct
- be concise, keep it as short as possible try to keep it below 3 sentences
- be motivational
- you can be humorous
- get more and more demanding with each message consecutively not reached goals

User name: Tim
EOT
            ),
            new UserMessage("Create a digest from these information: $this->digest"),
        ];

        $text = Denk::text()
            ->messages($messages)
            ->generate();

        return DiscordMessage::create($text);
    }
}
