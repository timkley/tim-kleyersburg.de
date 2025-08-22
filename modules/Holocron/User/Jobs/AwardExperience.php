<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Modules\Holocron\User\Enums\ExperienceType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

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
