<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Exercises;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise;
use Illuminate\View\View;

class Show extends HolocronComponent
{
    public Exercise $exercise;

    public function render(): View
    {
        $data = $this->exercise->volumePerWorkout()
            ->map(fn ($set): array => [
                'date' => $set->workout->finished_at,
                /** @phpstan-ignore-next-line */
                'total_volume' => $set->total_volume,
            ])->values()
            ->toArray();

        return view('holocron.grind.exercises.show', [
            'data' => $data,
        ]);
    }

    public function rendering(View $view): void
    {
        $view->title($this->exercise->name);
    }
}
