<?php

declare(strict_types=1);

namespace App\Notifications\Holocron;

use App\Models\Holocron\Health\DailyGoal;
use App\Notifications\Chopper;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class EveningDigest extends Notification
{
    use Queueable;

    public function __construct(public Collection $reachedGoals) {}

    public function via(object $notifiable): array
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        $reachedGoals = $this->reachedGoals
            ->map(function (DailyGoal $goal) {
                return "- {$goal->type->value}: du hast $goal->amount {$goal->type->unit()->value} erreicht";
            })->implode(PHP_EOL);

        $answer = Chopper::conversation(
            <<<EOT
Erstelle eine Abschlussnachricht für das Ende des Tages aus den folgenden Informationen:

$reachedGoals
EOT,
            'digest',
            CarbonImmutable::now()->endOfDay()
        );

        return DiscordMessage::create($answer);
    }
}
