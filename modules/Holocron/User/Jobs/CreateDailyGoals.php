<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;

class CreateDailyGoals implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(): void
    {
        foreach (GoalType::cases() as $type) {
            if ($type->deactivated()) {
                continue;
            }

            // asking for the daily goal will create it if it doesn't exist
            DailyGoal::for($type);
        }
    }
}
