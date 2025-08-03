<?php

declare(strict_types=1);

namespace App\Enums\Holocron\Gear;

enum Property: string
{
    case WarmWeather = 'warm-weather';
    case CoolWeather = 'cool-weather';
    case RainExpected = 'rain-expected';
    case ChildOnBoard = 'child-on-board';
}
