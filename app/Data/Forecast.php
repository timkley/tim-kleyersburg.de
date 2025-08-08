<?php

declare(strict_types=1);

namespace App\Data;

class Forecast
{
    /**
     * @param  float  $avgMinTemp  The average minimum temperature for the period.
     * @param  float  $avgMaxTemp  The average maximum temperature for the period.
     * @param  bool  $rainExpected  If rain is suspected in the period
     * @param  array<DayForecast>  $days  An array of daily forecast objects.
     */
    public function __construct(
        public float $avgMinTemp,
        public float $avgMaxTemp,
        public bool $rainExpected,
        public array $days,
    ) {}
}
