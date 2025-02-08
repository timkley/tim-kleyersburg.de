<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\Weather;
use Carbon\CarbonImmutable;
use Denk\Facades\Denk;
use Denk\ValueObjects\AssistantMessage;
use Denk\ValueObjects\DeveloperMessage;
use Denk\ValueObjects\UserMessage;

class Chopper
{
    public static function conversation(string $message, string $topic, ?CarbonImmutable $ttl = null): string
    {
        $history = cache("chopper.$topic", [
            new DeveloperMessage(self::personality()),
            new UserMessage($message),
        ]);

        $answer = Denk::text()
            ->messages($history)
            ->generate();

        $history[] = new AssistantMessage($answer);
        cache(["chopper.$topic" => $history], $ttl ?? now()->endOfDay());

        return $answer;
    }

    protected static function personality(): string
    {
        $date = now()->toDateString();
        $time = now()->toTimeString();
        $forecast = Weather::today();
        $condition = $forecast->condition;
        $maxTemp = $forecast->maxTemp;
        $minTemp = $forecast->minTemp;

        return
            <<<EOT
You are a helpful assistant named Chopper.
Today is the $date, it is currently $time, adjust the message accordingly.
The weather condition is "$condition", with a max temperature of $maxTemp and a min temperature of $minTemp.
You are talking to Tim.

- answer in german
- make sure german grammar and dictation is correct, don't answer before you are sure it is correct
- be concise, keep it as short as possible, try to keep it below 3 sentences
- be motivational
- you can be humorous
EOT;
    }
}
