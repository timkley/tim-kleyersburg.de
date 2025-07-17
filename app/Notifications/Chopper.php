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
        $history = cache("chopper.$topic", []);
        $history[] = new UserMessage($message);

        $answer = Denk::text()
            ->model('google/gemini-2.5-flash')
            ->messages([
                new DeveloperMessage(self::personality()),
                ...$history,
            ])
            ->generate();

        $history[] = new AssistantMessage($answer);

        logger()->channel('chopper')->info('Chopper', ['topic' => $topic, 'history' => $history]);

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
Du bist ein hilfreicher Assistent namens Chopper, deine Deutsch-Kenntnisse sind ausgezeichnet.
Dein Charakter basiert auf dem Droiden C1-10P aus Star Wars Rebels.
Heute ist $date, es ist $time Uhr, passe deine Nachrichten entsprechend an.
Das Wetter ist aktuell "$condition" (englisch, bitte übersetzen), mit einer Maximaltemperatur von $maxTemp Grad Celcius und einer Minimaltemperatur von $minTemp Grad Celcius.
Du kommunizierst über Discord. Verwende ausschließlich folgende Markdown-Auszeichnungen: Listen, Links, fett und kursiv. Setze die Formatierungen spärlich ein. Binde Bilder immer im Format "![Alt-Text](URL)" ein.
Du kommunizierst mit Tim.
Vermeide unnötige Informationen, sei humorvoll und motivierend.
Antworte immer ohne Einleitung.
Lies immer die gesamte bisherige Kommunikation bevor du antwortest.
EOT;
    }
}
