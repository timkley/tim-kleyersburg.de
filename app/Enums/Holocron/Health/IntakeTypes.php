<?php

declare(strict_types=1);

namespace App\Enums\Holocron\Health;

use App\Concerns\Holocron\Health\CalculatesGoals;

enum IntakeTypes: string
{
    use CalculatesGoals;

    case Water = 'water';
    case Creatine = 'creatine';
    case Planks = 'planks';

    public function unit(): IntakeUnits
    {
        return match ($this) {
            self::Water => IntakeUnits::Milliliters,
            self::Creatine => IntakeUnits::Grams,
            self::Planks => IntakeUnits::Seconds,
        };
    }
}
