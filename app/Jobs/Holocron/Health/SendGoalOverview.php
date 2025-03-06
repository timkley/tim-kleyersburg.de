<?php

declare(strict_types=1);

namespace App\Jobs\Holocron\Health;

use App\Models\Holocron\Health\DailyGoal;
use App\Notifications\Chopper;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use NotificationChannels\Discord\Discord;

class SendGoalOverview implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $goals = DailyGoal::whereDate('date', today())
            ->get()
            ->map(function (DailyGoal $goal) {
                $remaining = $goal->goal - $goal->amount;

                return sprintf(
                    '- das Ziel %s wurde %s %s',
                    $goal->type->value,
                    $goal->reached ? 'erreicht' : 'nicht erreicht',
                    ! $goal->reached ? ", es fehlen {$remaining} {$goal->type->unit()->value}" : ''
                );
            })->implode(PHP_EOL);

        $answer = Chopper::conversation(
            <<<EOT
Erstelle eine Nachricht zu den täglichen Zielen.
Übersetze die Ziele auf Deutsch.
Fokussiere dich auf NICHT abgeschlossene Ziele.
Nimm Bezug auf unsere Konversation, indem du anerkennst wenn Ziele fortgeschritten sind, bereits erreicht wurden aber auch, wenn sich bei der Zielerreichung nichts getan hat.
Ein erreichtes Ziel darf nicht öfter als einmal erwähnt werden.
Antworte nur mit der Nachricht.

Ziele:
$goals
EOT,
            'missed-goals',
            CarbonImmutable::now()->endOfDay()
        );

        /** @var Discord $discord */
        $discord = app(Discord::class);
        $discord->send(config('services.discord.tim_channel'), [
            'content' => $answer,
        ]);
    }
}
