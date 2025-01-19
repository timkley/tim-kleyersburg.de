<?php

declare(strict_types=1);

namespace App\Jobs\Holocron\Health;

use App\Models\Holocron\Health\DailyGoal;
use App\Notifications\DiscordTimChannel;
use App\Notifications\Holocron\Health\GoalsNotReached;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckGoals implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $goals = DailyGoal::whereDate('date', today())->whereColumn('amount', '<', 'goal')->get();

        (new DiscordTimChannel())->notify(new GoalsNotReached($goals));
    }
}
