<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Plans;

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Plan;

class Show extends HolocronComponent
{
    public Plan $plan;

    #[Validate('required')]
    public int $exerciseId;

    #[Validate('required|integer')]
    public int $sets;

    #[Validate('required|integer')]
    public int $minReps;

    #[Validate('required|integer')]
    public int $maxReps;

    #[Validate('required|integer')]
    public int $order;

    public function addExercise(): void
    {
        $this->validate();

        $this->plan->exercises()->syncWithoutDetaching([
            $this->exerciseId => [
                'sets' => $this->sets,
                'min_reps' => $this->minReps,
                'max_reps' => $this->maxReps,
                'order' => $this->order,
            ],
        ]);

        $this->reset(['exerciseId', 'sets', 'minReps', 'maxReps']);
    }

    public function removeExercise(int $exerciseId): void
    {
        $this->plan->exercises()->detach($exerciseId);

        Flux::toast('Übung entfernt.');
    }

    public function mount(Plan $plan): void
    {
        $this->plan = $plan;
    }

    public function render(): View
    {
        return view('holocron-grind::plans.show', [
            'availableExercises' => Exercise::all(),
        ])
            ->title($this->plan->name);
    }
}
