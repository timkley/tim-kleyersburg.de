<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Workouts\Components;

use Illuminate\View\View;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Workout;

class Timer extends HolocronComponent
{
    public Workout $workout;

    /** @var string[] */
    protected $listeners = [
        'set:started' => '$refresh',
        'set:finished' => '$refresh',
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

        return view('holocron-grind::workouts.components.timer', [
            'lastFinishedSet' => $lastFinishedSet ?? null,
        ]);
    }
}
