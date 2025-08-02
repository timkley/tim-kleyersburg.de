<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Workouts;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Plan;
use App\Models\Holocron\Grind\Set;
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
        $plan = Plan::with('exercises:id')->findOrFail($planId);

        $workout = Workout::create([
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

        $previousWorkout = Workout::query()->where('plan_id', $planId)->whereNotNull('finished_at')->limit(1)->latest()->first();

        if ($previousWorkout) {
            $previousSets = $previousWorkout->sets->map(function (Set $set) {
                return [
                    'exercise_id' => $set->workoutExercise->exercise_id,
                    'reps' => $set->reps,
                    'weight' => $set->weight,
                ];
            })->groupBy('exercise_id');

            // Create sets for each workout exercise
            foreach ($workout->exercises as $workoutExercise) {
                $exerciseId = $workoutExercise->exercise_id;

                if (isset($previousSets[$exerciseId])) {
                    $setsData = $previousSets[$exerciseId]->map(function ($set) {
                        return [
                            'weight' => $set['weight'],
                            'reps' => $set['reps'],
                        ];
                    })->toArray();

                    $workoutExercise->sets()->createMany($setsData);
                }
            }
        }

        $this->redirect(route('holocron.grind.workouts.show', $workout));
    }

    public function render(): View
    {
        return view('holocron.grind.workouts.index', [
            'plans' => Plan::all(),
            'unfinishedWorkouts' => Workout::query()->whereNull('finished_at')->latest()->get(),
            'pastWorkouts' => Workout::query()->limit(10)->latest('finished_at')->paginate(10),
        ]);
    }
}
