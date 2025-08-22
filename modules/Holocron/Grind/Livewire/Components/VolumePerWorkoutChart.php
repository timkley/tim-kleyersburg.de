<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Components;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Exercise;

class VolumePerWorkoutChart extends HolocronComponent
{
    public int $exerciseId;

    public function render(): View
    {
        /** @var Collection<int, array{date: string, total_volume: float|int}> $data */
        $data = Exercise::find($this->exerciseId)->volumePerWorkout()
            ->map(fn ($item): array => [
                'date' => Carbon::parse($item->workout_finished_at),
                'total_volume' => (float) $item->total_volume,
            ])
            ->values();

        $trendlineData = $this->calculateTrendline($data);

        $data = $data->map(function ($item, $index) use ($trendlineData) {
            $item['date'] = $item['date']->format('Y-m-d');
            $item['trendline'] = $trendlineData[$index];

            return $item;
        });

        return view('holocron-grind::components.volume-per-workout-chart', [
            'data' => $data->toArray(),
        ]);
    }

    private function calculateTrendline(Collection $data): array
    {
        $x = $data->map(fn ($item, $index) => $index)->toArray();
        $y = $data->pluck('total_volume')->toArray();

        if (count($x) < 2) {
            return array_fill(0, count($x), 0);
        }

        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumX2 = array_sum(array_map(fn ($val) => $val * $val, $x));
        $sumXY = array_sum(array_map(fn ($valX, $valY) => $valX * $valY, $x, $y));

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        return array_map(fn ($valX) => $slope * $valX + $intercept, $x);
    }
}
