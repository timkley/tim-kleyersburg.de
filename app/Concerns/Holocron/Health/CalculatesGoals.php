<?php

declare(strict_types=1);

namespace App\Concerns\Holocron\Health;

use App\Enums\Holocron\Health\IntakeTypes;
use App\Models\Holocron\Health\DailyGoal;
use App\Models\User;
use App\Services\Weather;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

trait CalculatesGoals
{
    public function goal(): int
    {
        return match ($this) {
            self::Water => $this->waterGoal(),
            self::Creatine => 5,
            self::Planks => $this->plankGoal(),
        };
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

    protected function waterGoal(): int
    {
        $user = User::where('email', 'timkley@gmail.com')->sole();
        $weight = $user->settings?->weight;
        $temperature = Weather::today()->maxTemp;
        $goal = $weight * 0.033;

        match (true) {
            $temperature >= 30 => $goal += 0.75,
            $temperature >= 25 => $goal += 0.5,
            default => null,
        };

        return (int) ($goal * 1000);
    }

    protected function plankGoal(): int
    {
        return max(90, DailyGoal::where('type', IntakeTypes::Planks)->max('amount') + 5);
    }
}
