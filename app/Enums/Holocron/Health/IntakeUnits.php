<?php

declare(strict_types=1);

namespace App\Enums\Holocron\Health;

enum IntakeUnits: string
{
    case Milliliters = 'ml';
    case Grams = 'g';
    case Pieces = 'pcs';
}
