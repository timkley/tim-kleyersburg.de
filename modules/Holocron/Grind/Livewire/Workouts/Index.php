<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Workouts;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Exercise;
use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Set;
use Modules\Holocron\Grind\Models\Workout;

#[Title('Workouts')]
class Index extends HolocronComponent
{
    use WithPagination;

    public function start(int $planId): void
    {
        $plan = Plan::with('exercises:id')->findOrFail($planId);

        $workout = Workout::query()->create([
            'plan_id' => $planId,
            'started_at' => now(),
        ]);

        $exercises = $plan->exercises()
            ->with('sets')
            ->get()
            ->map(function (Exercise $exercise) {
                return [
                    'exercise_id' => $exercise->id,
                    'sets' => $exercise->pivot->sets,
                    'min_reps' => $exercise->pivot->min_reps,
                    'max_reps' => $exercise->pivot->max_reps,
                    'order' => $exercise->pivot->order,
                ];
            });

        $workout->exercises()->createMany($exercises);

        // Prepopulate sets based on latest performance for each exercise
        foreach ($workout->exercises as $workoutExercise) {
            $latestSets = Set::query()
                ->latestForExercise($workoutExercise->exercise_id)
                ->limit($workoutExercise->sets)
                ->get(['weight', 'reps']);

            if ($latestSets->isNotEmpty()) {
                $setsData = $latestSets->reverse()->map(function (Set $set) {
                    return [
                        'weight' => $set->weight,
                        'reps' => $set->reps,
                    ];
                })->toArray();

                $workoutExercise->sets()->createMany($setsData);
            }
        }

        $this->redirect(route('holocron.grind.workouts.show', $workout));
    }

    public function render(): View
    {
        return view('holocron-grind::workouts.index', [
            'plans' => Plan::all(),
            'unfinishedWorkouts' => Workout::query()->whereNull('finished_at')->latest()->get(),
            'pastWorkouts' => Workout::query()->limit(10)->latest('finished_at')->paginate(10),
        ]);
    }
}
