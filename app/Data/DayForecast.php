<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

class DayForecast
{
    public function __construct(
        public CarbonImmutable $date,
        public float $minTemp,
        public float $maxTemp,
        public float $rain,
        public string $condition,
        public int $wmoCode,
    ) {}
}
