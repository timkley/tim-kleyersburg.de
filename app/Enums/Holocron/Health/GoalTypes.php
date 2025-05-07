<?php

declare(strict_types=1);

namespace App\Enums\Holocron\Health;

use App\Concerns\Holocron\Health\CalculatesGoals;

enum GoalTypes: string
{
    use CalculatesGoals;

    case Water = 'water';
    case Creatine = 'creatine';
    case Planks = 'planks';
    case Mobility = 'mobility';
    case NoSmoking = 'no_smoking';
    case NoAlcohol = 'no_alcohol';

    public function unit(): GoalUnits
    {
        return match ($this) {
            self::Water => GoalUnits::Milliliters,
            self::Creatine => GoalUnits::Grams,
            self::Planks => GoalUnits::Seconds,
            self::Mobility => GoalUnits::Boolean,
            self::NoSmoking => GoalUnits::Boolean,
            self::NoAlcohol => GoalUnits::Boolean,
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
