<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Holocron\User\Concerns\HasStreaks;
use Modules\Holocron\User\Database\Factories\DailyGoalFactory;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Enums\GoalUnit;

/**
 * @property GoalType $type
 * @property bool $reached
 */
class DailyGoal extends Model
{
    /** @use HasFactory<DailyGoalFactory> */
    use HasFactory, HasStreaks;

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

    public function track(int $amount): void
    {
        if ($amount === 0) {
            return;
        }

        $wasPreviouslyReached = $this->reached;

        $this->update(['amount' => $this->amount + $amount]);

        if ($this->reached) {
            $this->awardExperience();
        } elseif ($wasPreviouslyReached) {
            $this->retractExperience();
        }
    }

    protected static function newFactory(): DailyGoalFactory
    {
        return DailyGoalFactory::new();
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
