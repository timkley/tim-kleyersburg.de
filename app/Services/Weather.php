<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Forecast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Weather
{
    public static function today(string $query = 'Fellbach'): Forecast
    {
        $response = Cache::remember('weather', now()->addHours(2), fn () => Http::get('https://api.weatherapi.com/v1/forecast.json', [
            'key' => config('services.weatherapi.api_key'),
            'q' => $query,
            'days' => 1,
        ])->json());

        return new Forecast(
            minTemp: data_get($response, 'forecast.forecastday.0.day.mintemp_c'),
            maxTemp: data_get($response, 'forecast.forecastday.0.day.maxtemp_c'),
            condition: data_get($response, 'current.condition.text'),
        );
    }
}
