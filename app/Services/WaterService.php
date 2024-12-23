<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WaterIntake;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WaterService
{
    public static function goal(): float
    {
        $weight = auth()->user()->settings?->weight;
        $temperature = self::highestTemperature();
        $goal = $weight * 0.033;

        match (true) {
            $temperature >= 30 => $goal += 0.75,
            $temperature >= 25 => $goal += 0.5,
            default => null,
        };

        return $goal * 1000;
    }

    public static function dailyIntake()
    {
        return WaterIntake::whereDate('created_at', now())->sum('amount');
    }

    public static function remaining(): float|int
    {
        return self::goal() - self::dailyIntake();
    }

    public static function percentage(): float|int
    {
        return self::dailyIntake() / self::goal() * 100;
    }

    protected static function highestTemperature(string $query = 'Fellbach'): float
    {
        $response = Cache::remember('weather', now()->endOfDay(), function () use ($query) {
            return Http::get('https://api.weatherapi.com/v1/forecast.json', [
                'key' => config('services.weatherapi.api_key'),
                'q' => $query,
                'days' => 1,
            ])->json();
        });

        return data_get($response, 'forecast.forecastday.0.day.maxtemp_c');
    }
}
