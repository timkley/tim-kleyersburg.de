<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Workouts;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Plan;
use App\Models\Holocron\Grind\Workout;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Workouts')]
class Index extends HolocronComponent
{
    use WithPagination;

    public function start(int $planId): void
    {
        $previousWorkout = Workout::query()->where('plan_id', $planId)->limit(1)->latest()->first();
        $workout = Workout::create([
            'plan_id' => $planId,
            'started_at' => now(),
        ]);

        if ($previousWorkout) {
            $workout->sets()->createMany($previousWorkout->sets->map(fn ($set) => $set->only('exercise_id', 'weight', 'reps'))->toArray());
        }

        $this->redirect(route('holocron.grind.workouts.show', $workout));
    }

    public function render(): View
    {
        return view('holocron.grind.workouts.index', [
            'plans' => Plan::all(),
            'unfinishedWorkouts' => Workout::query()->whereNull('finished_at')->get(),
            'pastWorkouts' => Workout::query()->limit(10)->latest('finished_at')->paginate(10),
            'allWorkoutsCount' => Workout::query()->count(),
        ]);
    }
}
