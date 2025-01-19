<?php

declare(strict_types=1);

namespace App\Services;

class Forecast
{
    public float $minTemp;

    public float $maxTemp;

    public string $condition;

    public function __construct(array $response)
    {
        $this->minTemp = data_get($response, 'forecast.forecastday.0.day.mintemp_c');
        $this->maxTemp = data_get($response, 'forecast.forecastday.0.day.maxtemp_c');
        $this->condition = data_get($response, 'current.condition.text');
    }
}
