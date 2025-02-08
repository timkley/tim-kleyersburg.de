<?php

declare(strict_types=1);

namespace App\Models\Holocron\Health;

use App\Enums\Holocron\Health\GoalTypes;
use App\Enums\Holocron\Health\GoalUnits;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DailyGoal extends Model
{
    /** @use HasFactory<\Database\Factories\Holocron\Health\DailyGoalFactory> */
    use HasFactory;

    protected $casts = [
        'type' => GoalTypes::class,
        'unit' => GoalUnits::class,
    ];

    public static function for(GoalTypes $type)
    {
        return self::firstOrCreate(
            [
                'date' => today()->toDateString(),
                'type' => $type,
            ],
            [
                'unit' => $type->unit(),
                'goal' => $type->goal(),
                'amount' => $type->defaultAmount(),
            ]
        );
    }

    public static function currentStreakFor(GoalTypes $type): int
    {
        // Retrieve only the dates where the goal was met.
        $dates = self::where('type', $type)
            ->where('date', '<=', today()->toDateString())
            ->whereColumn('amount', '>=', 'goal')
            ->pluck('date')
            ->toArray();

        $dateSet = array_flip($dates);

        $streak = 0;
        $currentDate = today();

        if ($currentDate->isToday() && ! isset($dateSet[$currentDate->toDateString()])) {
            $currentDate = $currentDate->subDay();
        }

        while (isset($dateSet[$currentDate->toDateString()])) {
            $streak++;
            $currentDate = $currentDate->subDay();
        }

        return $streak;
    }

    public static function highestStreakFor(GoalTypes $type): int
    {
        // Retrieve valid goal dates (as Carbon objects) in ascending order.
        $dates = self::where('type', $type)
            ->where('date', '<=', today()->toDateString())
            ->whereColumn('amount', '>=', 'goal')
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d))
            ->all();

        if (empty($dates)) {
            return 0;
        }

        $highestStreak = 1;
        $currentStreak = 1;
        $previous = $dates[0];

        // Iterate over the dates (starting from the second record)
        foreach (array_slice($dates, 1) as $date) {
            // If this date is exactly one day after the previous date,
            // we continue the streak.
            if ($previous->copy()->addDay()->eq($date)) {
                $currentStreak++;
            } else {
                $currentStreak = 1; // Reset streak if gap found.
            }
            $highestStreak = max($highestStreak, $currentStreak);
            $previous = $date;
        }

        return $highestStreak;
    }

    protected function reached(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->amount >= $this->goal,
        );
    }
}
