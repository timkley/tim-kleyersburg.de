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

    public function unit(): GoalUnits
    {
        return match ($this) {
            self::Water => GoalUnits::Milliliters,
            self::Creatine => GoalUnits::Grams,
            self::Planks => GoalUnits::Seconds,
        };
    }
}
