<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Plans\Components;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Exercise as ExerciseModel;

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
        return view('holocron-grind::plans.components.exercise');
    }
}
