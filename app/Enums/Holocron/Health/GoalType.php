<?php

declare(strict_types=1);

namespace App\Enums\Holocron\Health;

use App\Concerns\Holocron\Health\CalculatesGoals;

enum GoalType: string
{
    use CalculatesGoals;

    case Water = 'water';
    case Creatine = 'creatine';
    case Planks = 'planks';
    case Mobility = 'mobility';
    case NoSmoking = 'no_smoking';
    case NoAlcohol = 'no_alcohol';
    case Protein = 'protein';

    public function unit(): GoalUnit
    {
        return match ($this) {
            self::Water => GoalUnit::Milliliters,
            self::Creatine => GoalUnit::Grams,
            self::Planks => GoalUnit::Seconds,
            self::Mobility => GoalUnit::Boolean,
            self::NoSmoking => GoalUnit::Boolean,
            self::NoAlcohol => GoalUnit::Boolean,
            self::Protein => GoalUnit::Grams,
        };
    }

    public function deactivated(): bool
    {
        return match ($this) {
            self::Planks => true,
            self::Creatine => true,
            default => false,
        };
    }
}
