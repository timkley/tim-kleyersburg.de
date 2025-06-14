<?php

declare(strict_types=1);

namespace App\Models\Holocron\Health;

use App\Enums\Holocron\ExperienceType;
use App\Enums\Holocron\Health\GoalType;
use App\Enums\Holocron\Health\GoalUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\Holocron\Health\DailyGoalFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property GoalType $type
 * @property bool $reached
 */
class DailyGoal extends Model
{
    /** @use HasFactory<DailyGoalFactory> */
    use HasFactory;

    public static function for(GoalType $type, ?CarbonImmutable $date = null): self
    {
        $date ??= today();

        $goal = self::query()
            ->where('date', $date->toDateString())
            ->where('type', $type)
            ->first();

        if (! $goal) {
            $goal = self::create([
                'date' => $date->toDateString(),
                'type' => $type,
                'unit' => $type->unit(),
                'goal' => $type->goal(),
            ]);

            $goal->track($type->defaultAmount());
        }

        return $goal;
    }

    public static function currentStreakFor(GoalType $type): int
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

    public static function highestStreakFor(GoalType $type): int
    {
        // Retrieve valid goal dates (as Carbon objects) in ascending order.
        $dates = self::where('type', $type)
            ->where('date', '<=', today()->toDateString())
            ->whereColumn('amount', '>=', 'goal')
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($d): Carbon => Carbon::parse($d))
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

    public function track(int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        $wasPreviouslyReached = $this->reached;

        $this->update([
            'amount' => $this->amount + $amount,
        ]);

        if ($this->reached) {
            User::tim()->addExperience(2, ExperienceType::GoalReached, $this->id);
        }

        if ($wasPreviouslyReached && ! $this->reached) {
            User::tim()->addExperience(-2, ExperienceType::GoalUnreached, $this->id);
        }
    }

    protected function casts(): array
    {
        return [
            'type' => GoalType::class,
            'unit' => GoalUnit::class,
        ];
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function reached(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->amount >= $this->goal,
        );
    }
}
