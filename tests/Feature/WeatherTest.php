<?php

declare(strict_types=1);

use App\Data\DayForecast;
use App\Data\Forecast;
use App\Services\Weather;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('returns a forecast for a near-future date range using the forecast api', function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => ['2026-03-01', '2026-03-02'],
                'weather_code' => [0, 61],
                'temperature_2m_max' => [12.5, 8.3],
                'temperature_2m_min' => [3.1, 1.2],
                'precipitation_sum' => [0.0, 5.2],
            ],
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-03-01');
    $end = CarbonImmutable::parse('2026-03-02');

    $forecast = Weather::forecast('Zurich', $start, $end);

    expect($forecast)->toBeInstanceOf(Forecast::class)
        ->and($forecast->days)->toHaveCount(2)
        ->and($forecast->avgMinTemp)->toBe(2.15)
        ->and($forecast->avgMaxTemp)->toBe(10.4)
        ->and($forecast->rainExpected)->toBeTrue();

    $firstDay = $forecast->days[0];
    expect($firstDay)->toBeInstanceOf(DayForecast::class)
        ->and($firstDay->condition)->toBe('Clear sky')
        ->and($firstDay->wmoCode)->toBe(0)
        ->and($firstDay->minTemp)->toBe(3.1)
        ->and($firstDay->maxTemp)->toBe(12.5)
        ->and($firstDay->rain)->toBe(0.0);

    $secondDay = $forecast->days[1];
    expect($secondDay->condition)->toBe('Slight rain')
        ->and($secondDay->rain)->toBe(5.2);
});

it('returns an empty forecast when geocoding fails', function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [],
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-03-01');
    $end = CarbonImmutable::parse('2026-03-02');

    $forecast = Weather::forecast('NonexistentPlace', $start, $end);

    expect($forecast)->toBeInstanceOf(Forecast::class)
        ->and($forecast->avgMinTemp)->toBe(0.0)
        ->and($forecast->avgMaxTemp)->toBe(0.0)
        ->and($forecast->rainExpected)->toBeFalse()
        ->and($forecast->days)->toBe([]);
});

it('returns an empty forecast when geocoding returns no results key', function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([]),
    ]);

    $start = CarbonImmutable::parse('2026-03-01');
    $end = CarbonImmutable::parse('2026-03-02');

    $forecast = Weather::forecast('Nowhere', $start, $end);

    expect($forecast->days)->toBe([]);
});

it('uses the historical api for dates far in the future', function () {
    // Set "now" so 2026-06-15 is more than 16 days away
    $this->travelTo(CarbonImmutable::parse('2026-02-27'));

    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'archive-api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => [
                    '2021-06-15', '2022-06-15', '2023-06-15', '2024-06-15', '2025-06-15',
                    '2021-06-16', '2022-06-16', '2023-06-16', '2024-06-16', '2025-06-16',
                ],
                'weather_code' => [0, 2, 0, 1, 0, 61, 63, 61, 61, 63],
                'temperature_2m_max' => [25.0, 24.0, 26.0, 23.0, 25.0, 20.0, 19.0, 21.0, 18.0, 20.0],
                'temperature_2m_min' => [12.0, 11.0, 13.0, 10.0, 12.0, 8.0, 7.0, 9.0, 6.0, 8.0],
                'precipitation_sum' => [0.0, 0.0, 0.0, 0.0, 0.0, 5.0, 6.0, 4.0, 7.0, 5.0],
            ],
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-06-15');
    $end = CarbonImmutable::parse('2026-06-16');

    $forecast = Weather::forecast('Zurich', $start, $end);

    expect($forecast)->toBeInstanceOf(Forecast::class)
        ->and($forecast->days)->toHaveCount(2)
        ->and($forecast->rainExpected)->toBeTrue();

    // Verify the historical API was called, not the forecast API
    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'archive-api.open-meteo.com');
    });

    Http::assertNotSent(function ($request) {
        return str_contains($request->url(), 'api.open-meteo.com/v1/forecast');
    });
});

it('handles weather api returning an error response', function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'error' => true,
            'reason' => 'Invalid parameters',
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-03-01');
    $end = CarbonImmutable::parse('2026-03-02');

    $forecast = Weather::forecast('Zurich', $start, $end);

    expect($forecast->days)->toBe([]);
});

it('handles weather api returning no daily data', function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'latitude' => 47.37,
            'longitude' => 8.55,
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-03-01');
    $end = CarbonImmutable::parse('2026-03-02');

    $forecast = Weather::forecast('Zurich', $start, $end);

    expect($forecast->days)->toBe([]);
});

it('caches geocoding results', function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => ['2026-03-01'],
                'weather_code' => [0],
                'temperature_2m_max' => [10.0],
                'temperature_2m_min' => [2.0],
                'precipitation_sum' => [0.0],
            ],
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-03-01');
    $end = CarbonImmutable::parse('2026-03-01');

    Weather::forecast('Zurich', $start, $end);

    // Clear weather cache but keep geocoding cache
    Cache::forget('weather:'.hash('sha256', 'https://api.open-meteo.com/v1/forecast'.http_build_query([
        'latitude' => 47.37,
        'longitude' => 8.55,
        'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum',
        'timezone' => 'auto',
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-01',
    ])));

    Weather::forecast('Zurich', $start, $end);

    // Geocoding should only be called once due to caching
    Http::assertSentCount(3); // 1 geocode + 2 forecast (cache was cleared for forecast)
});

it('returns empty forecast when historical api returns an error', function () {
    $this->travelTo(CarbonImmutable::parse('2026-02-27'));

    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'archive-api.open-meteo.com/*' => Http::response([
            'error' => true,
            'reason' => 'Invalid date range',
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-06-15');
    $end = CarbonImmutable::parse('2026-06-16');

    $forecast = Weather::forecast('Zurich', $start, $end);

    expect($forecast->days)->toBe([]);
});

it('returns empty forecast when historical api returns no daily data', function () {
    $this->travelTo(CarbonImmutable::parse('2026-02-27'));

    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'archive-api.open-meteo.com/*' => Http::response([
            'latitude' => 47.37,
            'longitude' => 8.55,
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-06-15');
    $end = CarbonImmutable::parse('2026-06-16');

    $forecast = Weather::forecast('Zurich', $start, $end);

    expect($forecast->days)->toBe([]);
});

it('translates wmo codes correctly in forecast results', function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => ['2026-03-01', '2026-03-02', '2026-03-03', '2026-03-04'],
                'weather_code' => [0, 3, 65, 95],
                'temperature_2m_max' => [10.0, 8.0, 6.0, 7.0],
                'temperature_2m_min' => [2.0, 1.0, 0.0, 1.0],
                'precipitation_sum' => [0.0, 0.0, 15.0, 3.0],
            ],
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-03-01');
    $end = CarbonImmutable::parse('2026-03-04');

    $forecast = Weather::forecast('Zurich', $start, $end);

    expect($forecast->days[0]->condition)->toBe('Clear sky')
        ->and($forecast->days[1]->condition)->toBe('Overcast')
        ->and($forecast->days[2]->condition)->toBe('Heavy rain')
        ->and($forecast->days[3]->condition)->toBe('Thunderstorm');
});

it('translates all wmo weather codes', function (int $code, string $expectedCondition) {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 47.37, 'longitude' => 8.55],
            ],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => ['2026-03-01'],
                'weather_code' => [$code],
                'temperature_2m_max' => [10.0],
                'temperature_2m_min' => [2.0],
                'precipitation_sum' => [0.0],
            ],
        ]),
    ]);

    $start = CarbonImmutable::parse('2026-03-01');
    $end = CarbonImmutable::parse('2026-03-01');

    $forecast = Weather::forecast('Zurich', $start, $end);

    expect($forecast->days[0]->condition)->toBe($expectedCondition);
})->with([
    'mainly clear' => [1, 'Mainly clear'],
    'partly cloudy' => [2, 'Partly cloudy'],
    'fog' => [45, 'Fog'],
    'depositing rime fog' => [48, 'Depositing rime fog'],
    'light drizzle' => [51, 'Light drizzle'],
    'moderate drizzle' => [53, 'Moderate drizzle'],
    'dense drizzle' => [55, 'Dense drizzle'],
    'light freezing drizzle' => [56, 'Light freezing drizzle'],
    'dense freezing drizzle' => [57, 'Dense freezing drizzle'],
    'slight rain' => [61, 'Slight rain'],
    'moderate rain' => [63, 'Moderate rain'],
    'light freezing rain' => [66, 'Light freezing rain'],
    'heavy freezing rain' => [67, 'Heavy freezing rain'],
    'slight snow fall' => [71, 'Slight snow fall'],
    'moderate snow fall' => [73, 'Moderate snow fall'],
    'heavy snow fall' => [75, 'Heavy snow fall'],
    'snow grains' => [77, 'Snow grains'],
    'slight rain showers' => [80, 'Slight rain showers'],
    'moderate rain showers' => [81, 'Moderate rain showers'],
    'violent rain showers' => [82, 'Violent rain showers'],
    'slight snow showers' => [85, 'Slight snow showers'],
    'heavy snow showers' => [86, 'Heavy snow showers'],
    'thunderstorm with slight hail' => [96, 'Thunderstorm with slight hail'],
    'thunderstorm with heavy hail' => [99, 'Thunderstorm with heavy hail'],
    'unknown code' => [999, 'Unknown'],
]);
