<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Workouts;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Workout;
use Illuminate\View\View;

class Timer extends HolocronComponent
{
    public Workout $workout;

    /** @var string[] */
    protected $listeners = [
        'set:started' => '$refresh',
        'set:stopped' => '$refresh',
    ];

    public function render(): View
    {
        $training = $this->workout->sets()
            ->whereNotNull('started_at')
            ->whereNull('finished_at')
            ->limit(1)
            ->latest('updated_at')
            ->first();

        if (! $training) {
            $lastFinishedSet = $this->workout->sets()
                ->whereNotNull('started_at')
                ->whereNotNull('finished_at')
                ->limit(1)
                ->latest('updated_at')
                ->first();
        }

        return view('holocron.grind.workouts.timer', [
            'lastFinishedSet' => $lastFinishedSet ?? null,
        ]);
    }
}
