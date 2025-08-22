<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Components;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
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
                'date' => Carbon::parse($item->workout_completed_at)->format('Y-m-d'),
                'total_volume' => (float) $item->total_volume,
            ])
            ->values();

        return view('holocron-grind::components.volume-per-workout-chart', [
            'data' => $data->toArray(),
        ]);
    }
}
