<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WaterIntake;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WaterService
{
    public static function goal(): int
    {
        $weight = auth()->user()->settings?->weight;
        $temperature = self::highestTemperature();
        $goal = $weight * 0.033;

        match (true) {
            $temperature >= 30 => $goal += 0.75,
            $temperature >= 25 => $goal += 0.5,
            default => null,
        };

        return (int) ($goal * 1000);
    }

    public static function todaysIntake(): int
    {
        return WaterIntake::whereDate('created_at', now())->sum('amount');
    }

    public static function remaining(): int
    {
        return self::goal() - self::todaysIntake();
    }

    public static function percentage(): int
    {
        if (self::goal() === 0) {
            return 0;
        }

        return (int) (self::todaysIntake() / self::goal() * 100);
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
