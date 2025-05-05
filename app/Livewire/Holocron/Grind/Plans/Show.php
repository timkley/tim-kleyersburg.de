<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Plans;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Plan;
use Illuminate\View\View;
use Livewire\Attributes\Validate;

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

    public function mount(Plan $plan): void
    {
        $this->plan = $plan;
    }

    public function render(): View
    {
        return view('holocron.grind.plans.show', [
            'availableExercises' => Exercise::all(),
        ])
            ->title($this->plan->name);
    }
}
