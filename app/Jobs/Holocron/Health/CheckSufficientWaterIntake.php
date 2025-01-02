<?php

declare(strict_types=1);

namespace App\Jobs\Holocron\Health;

use App\Notifications\DiscordTimChannel;
use App\Notifications\Holocron\Health\InsufficientWaterIntake;
use App\Services\WaterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckSufficientWaterIntake implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $actual = WaterService::percentage();
        $remaining = WaterService::remaining();
        $expected = $this->expectedPercentage();

        if ($actual < $expected) {
            (new DiscordTimChannel())->notify(new InsufficientWaterIntake($remaining));
        }
    }

    protected function expectedPercentage(): int
    {
        $wakingStart = now()->setTime(6, 0);
        $wakingEnd = now()->setTime(22, 0);
        // If the current time is before waking hours
        if (now()->lt($wakingStart)) {
            return 0;
        }

        // If the current time is after waking hours
        if (now()->gt($wakingEnd)) {
            return 100;
        }

        // Calculate the proportion of the day that has passed
        $wakingHours = $wakingEnd->diffInMinutes($wakingStart);
        $elapsedMinutes = now()->diffInMinutes($wakingStart);

        return (int) (($elapsedMinutes / $wakingHours) * 100);
    }
}
