<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Services\Weather;
use Carbon\CarbonImmutable;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class Chopper
{
    public static function conversation(string $message, string $topic, ?CarbonImmutable $ttl = null): string
    {
        $history = cache("chopper.$topic", []);
        $history[] = new UserMessage($message);

        $response = Prism::text()
            ->using(Provider::OpenRouter, 'google/gemini-2.5-flash')
            ->withSystemPrompt(self::personality())
            ->withMessages(...$history)
            ->asText();

        $answer = $response->text;

        $history[] = new AssistantMessage($answer);

        logger()->channel('chopper')->info('Chopper', ['topic' => $topic, 'history' => $history]);

        cache(["chopper.$topic" => $history], $ttl ?? now()->endOfDay());

        return $answer;
    }

    protected static function personality(): string
    {
        $date = now()->format('l, j. F Y');
        $time = now()->toTimeString();
        $forecast = Weather::forecast('Fellbach', CarbonImmutable::now(), CarbonImmutable::now());
        $condition = $forecast->days[0]->condition ?? 'Unknown';
        $maxTemp = $forecast->days[0]->maxTemp ?? 'Unknown';
        $minTemp = $forecast->days[0]->minTemp ?? 'Unknown';

        return
            <<<EOT
Du bist ein hilfreicher Assistent namens Chopper.
Dein Charakter basiert auf dem Droiden C1-10P aus Star Wars Rebels.
Heute ist $date, es ist $time Uhr, passe deine Nachrichten wenn sinnvoll entsprechend an.
Das Wetter ist aktuell "$condition" (englisch, bitte übersetzen), mit einer Maximaltemperatur von $maxTemp Grad Celcius und einer Minimaltemperatur von $minTemp Grad Celcius.
Du kommunizierst über Discord. Verwende ausschließlich folgende Markdown-Auszeichnungen: Listen, Links, fett und kursiv. Setze die Formatierungen spärlich ein. Binde Bilder immer im Format "![Alt-Text](URL)" ein.
Du kommunizierst mit Tim.
Vermeide unnötige Informationen, sei humorvoll und motivierend.
Antworte immer ohne Einleitung.
Lies immer die gesamte bisherige Kommunikation bevor du antwortest.
EOT;
    }
}
