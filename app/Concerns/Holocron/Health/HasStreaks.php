<?php

declare(strict_types=1);

namespace App\Concerns\Holocron\Health;

use App\Enums\Holocron\ExperienceType;
use App\Enums\Holocron\Health\GoalType;
use App\Models\User;
use Illuminate\Support\Carbon;

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

    private function awardExperience(): void
    {
        $currentStreak = self::currentStreakFor($this->type);
        $baseXp = 2;
        $scaledXp = (int) round($baseXp + $currentStreak * 0.1);

        User::tim()->addExperience($scaledXp, ExperienceType::GoalReached, $this->id);

        $this->checkAndAwardStreakGoal($currentStreak);
    }

    private function checkAndAwardStreakGoal(int $currentStreak): void
    {
        $streakGoals = $this->getStreakGoals();

        if (in_array($currentStreak, $streakGoals)) {
            $xp = (int) (5 + ($currentStreak / 2));
            $identifier = crc32($this->type->value.'_streak_'.$currentStreak);
            User::tim()->addExperience($xp, ExperienceType::StreakGoalReached, $identifier);
        }
    }

    private function retractExperience(): void
    {
        User::tim()->addExperience(-2, ExperienceType::GoalUnreached, $this->id);
    }

    /**
     * @return array<int, int>
     */
    private function getStreakGoals(): array
    {
        return collect(range(1, 40))->flatMap(fn ($i) => [
            $i * 5,
        ])->all();
    }
}
