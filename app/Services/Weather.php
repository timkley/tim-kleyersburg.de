<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\DayForecast;
use App\Data\Forecast;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Weather
{
    private const int FORECAST_API_MAX_DAYS_AHEAD = 16;

    private const int HISTORICAL_YEARS_TO_AVERAGE = 5;

    private const string FORECAST_API_URL = 'https://api.open-meteo.com/v1/forecast';

    private const string HISTORICAL_API_URL = 'https://archive-api.open-meteo.com/v1/archive';

    private const string GEOCODING_API_URL = 'https://geocoding-api.open-meteo.com/v1/search';

    /**
     * @param  string  $query  Location query (e.g., "Zurich")
     * @param  CarbonImmutable  $startDate  The start date for the forecast
     * @param  CarbonImmutable  $endDate  The end date for the forecast
     */
    public static function forecast(string $query, CarbonImmutable $startDate, CarbonImmutable $endDate): Forecast
    {
        $coordinates = self::geocode($query);

        if (! $coordinates) {
            return new Forecast(avgMinTemp: 0, avgMaxTemp: 0, rainExpected: false, days: []);
        }

        $daysFromNow = now()->startOfDay()->diffInDays($startDate, false);

        $forecasts = ($daysFromNow < self::FORECAST_API_MAX_DAYS_AHEAD)
            ? self::fetchForecast($coordinates, $startDate, $endDate)
            : self::fetchHistoricalAverageForecast($coordinates, $startDate, $endDate);

        return new Forecast(
            avgMinTemp: $forecasts->avg('minTemp') ?? 0,
            avgMaxTemp: $forecasts->avg('maxTemp') ?? 0,
            rainExpected: $forecasts->sum('rain') > 0,
            days: $forecasts->all(),
        );
    }

    /**
     * @return array{latitude:float,longitude:float}|null
     */
    private static function geocode(string $query): ?array
    {
        $cacheKey = 'weather:geocode:'.mb_strtolower(str_replace(' ', '-', $query));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($query) {
            $response = Http::retry(3, 1000)
                ->get(self::GEOCODING_API_URL, [
                    'name' => $query,
                    'count' => 1,
                    'language' => 'de',
                    'format' => 'json',
                ])->json();

            $result = data_get($response, 'results.0');

            if (! $result) {
                Log::error('Geocoding API failed', ['query' => $query, 'response' => $response]);

                return null;
            }

            return [
                'latitude' => $result['latitude'],
                'longitude' => $result['longitude'],
            ];
        });
    }

    /**
     * @param  array{latitude:float,longitude:float}  $coordinates
     * @return Collection<int, DayForecast>
     */
    private static function fetchForecast(array $coordinates, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        $params = [
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum',
            'timezone' => 'auto',
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
        ];

        $response = self::makeApiCall(self::FORECAST_API_URL, $params);

        if (! $response || ! isset($response['daily'])) {
            return collect();
        }

        return self::processApiData($response['daily']);
    }

    /**
     * @param  array{latitude:float,longitude:float}  $coordinates
     * @return Collection<int, DayForecast>
     */
    private static function fetchHistoricalAverageForecast(array $coordinates, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        $historicalStartDate = $start->subYears(self::HISTORICAL_YEARS_TO_AVERAGE);
        $historicalEndDate = $end->subYears(1);

        $params = [
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum',
            'start_date' => $historicalStartDate->format('Y-m-d'),
            'end_date' => $historicalEndDate->format('Y-m-d'),
        ];

        $response = self::makeApiCall(self::HISTORICAL_API_URL, $params);

        if (! $response || ! isset($response['daily'])) {
            return collect();
        }

        return self::processHistoricalData($response['daily'], $start, $end);
    }

    /**
     * @param  array<string,mixed>  $params
     * @return array<string,mixed>|null
     */
    private static function makeApiCall(string $url, array $params): ?array
    {
        $cacheKey = 'weather:'.hash('sha256', $url.http_build_query($params));

        $response = Cache::remember($cacheKey, now()->addHours(4), function () use ($url, $params) {
            return Http::retry(3, 1000)->get($url, $params)->json();
        });

        if (! $response || isset($response['error'])) {
            Log::error('Weather API failed', [
                'url' => $url,
                'params' => $params,
                'error' => $response['error'] ?? 'empty_response',
                'reason' => $response['reason'] ?? 'N/A',
            ]);

            return null;
        }

        return $response;
    }

    /**
     * @param  array<string,mixed>  $dailyData
     * @return Collection<int, DayForecast>
     */
    private static function processApiData(array $dailyData): Collection
    {
        $forecasts = collect();
        $count = count($dailyData['time']);

        for ($i = 0; $i < $count; $i++) {
            $forecasts->push(new DayForecast(
                date: CarbonImmutable::parse($dailyData['time'][$i]),
                minTemp: $dailyData['temperature_2m_min'][$i],
                maxTemp: $dailyData['temperature_2m_max'][$i],
                rain: $dailyData['precipitation_sum'][$i],
                condition: self::translateWmoCode((int) $dailyData['weather_code'][$i]),
                wmoCode: $dailyData['weather_code'][$i],
            ));
        }

        return $forecasts;
    }

    /**
     * @param  array<string,mixed>  $historicalData
     * @return Collection<int, DayForecast>
     */
    private static function processHistoricalData(array $historicalData, CarbonImmutable $originalStart, CarbonImmutable $originalEnd): Collection
    {
        $dailyAggregates = [];

        // Aggregate historical data by month and day
        foreach ($historicalData['time'] as $index => $dateString) {
            $monthDay = CarbonImmutable::parse($dateString)->format('m-d');
            $dailyAggregates[$monthDay]['minTemp'][] = $historicalData['temperature_2m_min'][$index];
            $dailyAggregates[$monthDay]['maxTemp'][] = $historicalData['temperature_2m_max'][$index];
            $dailyAggregates[$monthDay]['rain'][] = $historicalData['precipitation_sum'][$index];
            $dailyAggregates[$monthDay]['codes'][] = $historicalData['weather_code'][$index];
        }

        $results = collect();
        $currentDate = $originalStart->copy();

        while ($currentDate->lte($originalEnd)) {
            $monthDay = $currentDate->format('m-d');

            if (isset($dailyAggregates[$monthDay])) {
                $dayData = $dailyAggregates[$monthDay];
                // Find the most frequent weather code (mode)
                $modeCode = array_count_values($dayData['codes']);
                arsort($modeCode);

                $results->push(new DayForecast(
                    date: $currentDate,
                    minTemp: array_sum($dayData['minTemp']) / count($dayData['minTemp']),
                    maxTemp: array_sum($dayData['maxTemp']) / count($dayData['maxTemp']),
                    rain: array_sum($dayData['rain']) / count($dayData['rain']),
                    condition: self::translateWmoCode((int) key($modeCode)),
                    wmoCode: (int) key($modeCode),
                ));
            }

            $currentDate = $currentDate->addDay();
        }

        return $results;
    }

    private static function translateWmoCode(int $code): string
    {
        return match ($code) {
            0 => 'Clear sky',
            1 => 'Mainly clear',
            2 => 'Partly cloudy',
            3 => 'Overcast',
            45 => 'Fog',
            48 => 'Depositing rime fog',
            51 => 'Light drizzle',
            53 => 'Moderate drizzle',
            55 => 'Dense drizzle',
            56 => 'Light freezing drizzle',
            57 => 'Dense freezing drizzle',
            61 => 'Slight rain',
            63 => 'Moderate rain',
            65 => 'Heavy rain',
            66 => 'Light freezing rain',
            67 => 'Heavy freezing rain',
            71 => 'Slight snow fall',
            73 => 'Moderate snow fall',
            75 => 'Heavy snow fall',
            77 => 'Snow grains',
            80 => 'Slight rain showers',
            81 => 'Moderate rain showers',
            82 => 'Violent rain showers',
            85 => 'Slight snow showers',
            86 => 'Heavy snow showers',
            95 => 'Thunderstorm',
            96 => 'Thunderstorm with slight hail',
            99 => 'Thunderstorm with heavy hail',
            default => 'Unknown',
        };
    }
}
