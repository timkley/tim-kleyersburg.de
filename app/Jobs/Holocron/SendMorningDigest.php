<?php

declare(strict_types=1);

namespace App\Jobs\Holocron;

use App\Notifications\DiscordTimChannel;
use App\Notifications\Holocron\DailyDigest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMorningDigest implements ShouldQueue
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
        $digest = cache('daily-digest');

        if (! $digest) {
            return;
        }

        (new DiscordTimChannel)->notify(new DailyDigest($digest));
    }
}
