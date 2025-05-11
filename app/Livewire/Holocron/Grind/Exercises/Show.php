<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Exercises;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Show extends HolocronComponent
{
    public Exercise $exercise;

    public function render(): View
    {
        /** @var Collection<int, array{date: string, total_volume: float|int}> */
        $data = $this->exercise->volumePerWorkout()
            ->map(fn ($set): array => [
                'date' => $set->workout->finished_at,
                'total_volume' => (float) $set->total_volume,
            ])
            ->values();

        return view('holocron.grind.exercises.show', [
            'data' => $data->toArray(),
        ]);
    }

    public function rendering(View $view): void
    {
        $view->title($this->exercise->name);
    }
}
