<?php

declare(strict_types=1);

namespace App\Jobs\Holocron\Health;

use App\Enums\Holocron\Health\IntakeTypes;
use App\Models\Holocron\Health\DailyGoal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateDailyGoals implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        foreach (IntakeTypes::cases() as $type) {
            // asking for the daily goal will create it if it doesn't exist
            DailyGoal::for($type);
        }
    }
}
