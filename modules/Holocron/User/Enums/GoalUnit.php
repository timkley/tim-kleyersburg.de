<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Enums;

enum GoalUnit: string
{
    case Milliliters = 'ml';
    case Grams = 'g';
    case Pieces = 'pcs';
    case Seconds = 's';
    case Boolean = 'bool';
}
