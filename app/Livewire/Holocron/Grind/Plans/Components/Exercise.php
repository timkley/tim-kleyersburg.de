<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Plans\Components;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise as ExerciseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class Exercise extends HolocronComponent
{
    public ExerciseModel $exercise;

    public int $exerciseId;

    public int $planId;

    public int $sets;

    public int $min_reps;

    public int $max_reps;

    public int $order;

    public function updated(string $property, string $value): void
    {
        DB::table('grind_exercise_plan')
            ->where('exercise_id', $this->exerciseId)
            ->where('plan_id', $this->planId)
            ->update([$property => $value]);
    }

    public function mount(): void
    {
        $this->exerciseId = $this->exercise->pivot->exercise_id;
        $this->planId = $this->exercise->pivot->plan_id;
        $this->sets = $this->exercise->pivot->sets;
        $this->min_reps = $this->exercise->pivot->min_reps;
        $this->max_reps = $this->exercise->pivot->max_reps;
        $this->order = $this->exercise->pivot->order;
    }

    public function render(): View
    {
        return view('holocron.grind.plans.components.exercise');
    }
}
