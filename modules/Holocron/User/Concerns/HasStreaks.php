<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Concerns;

use Illuminate\Support\Carbon;
use Modules\Holocron\User\Enums\GoalType;

trait HasStreaks
{
    public static function currentStreakFor(GoalType $type): int
    {
        $dates = self::getReachedGoalDates($type);
        $streak = 0;
        $currentDate = today();

        if ($currentDate->isToday() && ! in_array($currentDate->toDateString(), $dates)) {
            $currentDate = $currentDate->subDay();
        }

        while (in_array($currentDate->toDateString(), $dates)) {
            $streak++;
            $currentDate = $currentDate->subDay();
        }

        return $streak;
    }

    public static function highestStreakFor(GoalType $type): int
    {
        $dates = self::getReachedGoalDates($type, 'asc');

        if (empty($dates)) {
            return 0;
        }

        $highestStreak = 1;
        $currentStreak = 1;
        $previous = Carbon::parse($dates[0]);

        foreach (array_slice($dates, 1) as $date) {
            $current = Carbon::parse($date);
            if ($previous->copy()->addDay()->eq($current)) {
                $currentStreak++;
            } else {
                $currentStreak = 1;
            }
            $highestStreak = max($highestStreak, $currentStreak);
            $previous = $current;
        }

        return $highestStreak;
    }

    /**
     * @return array<int, string>
     */
    private static function getReachedGoalDates(GoalType $type, string $order = 'desc'): array
    {
        return self::query()
            ->where('type', $type)
            ->where('date', '<=', today()->toDateString())
            ->whereColumn('amount', '>=', 'goal')
            ->orderBy('date', $order)
            ->pluck('date')
            ->toArray();
    }
}
