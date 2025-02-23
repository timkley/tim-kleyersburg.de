<?php

declare(strict_types=1);

namespace App\Jobs\Holocron;

use App\Models\Holocron\Health\DailyGoal;
use App\Notifications\Chopper;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use NotificationChannels\Discord\Discord;

class SendEveningDigest implements ShouldQueue
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
        $reachedGoals = DailyGoal::whereDate('date', today())
            ->whereColumn('amount', '>=', 'goal')
            ->get()
            ->map(function (DailyGoal $goal) {
                return "- {$goal->type->value}: du hast $goal->amount {$goal->type->unit()->value} erreicht";
            })->implode(PHP_EOL);

        $answer = Chopper::conversation(
            <<<EOT
Erstelle eine Abschlussnachricht für das Ende des Tages aus den folgenden Informationen.
Antworte ausschließlich mit dem Abschlussbericht.

Erreichte Ziele:
$reachedGoals
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
