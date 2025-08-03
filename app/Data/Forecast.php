<?php

declare(strict_types=1);

namespace App\Data;

class Forecast
{
    /**
     * @param  array<string,mixed>  $raw
     */
    public function __construct(public float $minTemp, public float $maxTemp, public string $condition, public string $conditionIcon, public array $raw) {}
}
