<?php

declare(strict_types=1);

namespace App\Data;

class Forecast
{
    public function __construct(public float $minTemp, public float $maxTemp, public string $condition) {}
}
