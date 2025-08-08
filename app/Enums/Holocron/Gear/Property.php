<?php

declare(strict_types=1);

namespace App\Enums\Holocron\Gear;

use App\Models\Holocron\Gear\Journey;

enum Property: string
{
    case WarmWeather = 'warm-weather';
    case CoolWeather = 'cool-weather';
    case RainExpected = 'rain-expected';
    case ChildOnBoard = 'child-on-board';

    public function meetsCondition(Journey $journey): bool
    {
        $functionName = str('meets-'.$this->value)->camel()->toString();

        return $this->{$functionName}($journey);
    }

    private function meetsWarmWeather(Journey $journey): bool
    {
        $forecast = $journey->forecast();

        return $forecast->avgMaxTemp > 22;
    }

    private function meetsCoolWeather(Journey $journey): bool
    {
        $forecast = $journey->forecast();

        return $forecast->avgMinTemp < 8;
    }

    private function meetsRainExpected(Journey $journey): bool
    {
        $forecast = $journey->forecast();

        return $forecast->rainExpected;
    }

    private function meetsChildOnBoard(Journey $journey): bool
    {
        return in_array('kid', $journey->participants);
    }
}
