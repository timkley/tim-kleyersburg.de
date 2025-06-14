<?php

declare(strict_types=1);

namespace App\Enums\Holocron\Health;

enum GoalUnit: string
{
    case Milliliters = 'ml';
    case Grams = 'g';
    case Pieces = 'pcs';
    case Seconds = 's';
    case Boolean = 'bool';
}
