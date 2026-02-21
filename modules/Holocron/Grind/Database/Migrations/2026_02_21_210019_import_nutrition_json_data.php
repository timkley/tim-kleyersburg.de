<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Modules\Holocron\Grind\Models\BodyMeasurement;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        $path = base_path('nutrition.json');

        if (! file_exists($path)) {
            return;
        }

        $data = json_decode(file_get_contents($path), true);

        $this->importDailyTargets($data);
        $this->importBodyMeasurements($data);
        $this->importNutritionDays($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function importDailyTargets(array $data): void
    {
        if (! isset($data['profile']['dailyTargets'])) {
            return;
        }

        $user = User::first();

        if (! $user) {
            return;
        }

        $settings = $user->settings;

        if (! $settings) {
            $settings = $user->settings()->create();
        }

        $settings->update([
            'nutrition_daily_targets' => $data['profile']['dailyTargets'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function importBodyMeasurements(array $data): void
    {
        if (! isset($data['profile']['measurements'])) {
            return;
        }

        foreach ($data['profile']['measurements'] as $measurement) {
            BodyMeasurement::query()->updateOrCreate(
                ['date' => CarbonImmutable::parse($measurement['date'])],
                [
                    'weight' => $measurement['weight'],
                    'body_fat' => $measurement['bodyFat'] ?? null,
                    'muscle_mass' => $measurement['muscleMass'] ?? null,
                    'visceral_fat' => $measurement['visceralFat'] ?? null,
                    'bmi' => $measurement['bmi'] ?? null,
                    'body_water' => $measurement['bodyWater'] ?? null,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function importNutritionDays(array $data): void
    {
        $days = $data['days'] ?? [];

        // Also collect date-keyed entries at the root level
        foreach ($data as $key => $value) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $key) && is_array($value)) {
                $days[$key] = $value;
            }
        }

        foreach ($days as $date => $day) {
            NutritionDay::query()->updateOrCreate(
                ['date' => CarbonImmutable::parse($date)],
                [
                    'type' => $day['type'],
                    'training_label' => $day['training'] ?? null,
                    'meals' => $day['meals'] ?? [],
                    'notes' => $day['notes'] ?? null,
                    'total_kcal' => $day['totals']['kcal'] ?? 0,
                    'total_protein' => $day['totals']['protein'] ?? 0,
                    'total_fat' => $day['totals']['fat'] ?? 0,
                    'total_carbs' => $day['totals']['carbs'] ?? 0,
                ],
            );
        }
    }
};
