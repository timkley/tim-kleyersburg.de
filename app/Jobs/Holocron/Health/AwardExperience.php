<?php

declare(strict_types=1);

namespace App\Jobs\Holocron\Health;

use App\Enums\Holocron\ExperienceType;
use App\Models\Holocron\Health\DailyGoal;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AwardExperience implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(): void
    {
        if (! DailyGoal::query()->whereToday('date')->get()->pluck('reached')->contains(false)) {
            User::tim()->addExperience(5, ExperienceType::PerfectDay, (int) now()->startOfDay()->timestamp);
        }
    }
}
