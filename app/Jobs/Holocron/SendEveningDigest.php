<?php

declare(strict_types=1);

namespace App\Jobs\Holocron;

use App\Models\Holocron\Health\DailyGoal;
use App\Notifications\DiscordTimChannel;
use App\Notifications\Holocron\EveningDigest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
        $reachedGoals = DailyGoal::whereDate('date', today())->whereColumn('amount', '>=', 'goal')->get();

        (new DiscordTimChannel)->notify(new EveningDigest($reachedGoals));
    }
}
