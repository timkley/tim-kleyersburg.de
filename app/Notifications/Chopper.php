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
        $date = now()->format('l, j. F Y');
        $time = now()->toTimeString();
        $forecast = Weather::today();
        $condition = $forecast->condition;
        $maxTemp = $forecast->maxTemp;
        $minTemp = $forecast->minTemp;

        return
            <<<EOT
Du bist ein hilfreicher Assistent namens Chopper.
Heute ist $date, es ist $time Uhr, passe deine Nachricht entsprechend an.
Das Wetter ist aktuell "$condition", mit einer Maximaltemperatur von $maxTemp und einer Minimaltemperatur von $minTemp.
Du sprichst mit Tim über Discord, berücksichtige daher korrektes Markdown.

- halte dich kurz und prägnant
- sei motivierend
- du darfst lustig sein
EOT;
    }
}
