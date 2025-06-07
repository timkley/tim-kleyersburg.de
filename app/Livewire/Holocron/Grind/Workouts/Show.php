<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Workouts;

use App\Enums\Holocron\ExperienceType;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Workout;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Workouts')]
class Show extends HolocronComponent
{
    public Workout $workout;

    #[Validate('required|numeric')]
    public float $weight;

    #[Validate('required|int')]
    public int $reps;

    public function setExercise(int $index): void
    {
        $this->workout->update([
            'current_exercise_index' => $index,
        ]);
    }

    public function recordSet(): void
    {
        $this->validate();

        $this->workout->sets()->create([
            'exercise_id' => $this->workout->getCurrentExercise()->id,
            'weight' => $this->weight,
            'reps' => $this->reps,
        ]);

        $this->reset('weight', 'reps');
    }

    public function finish(): void
    {
        $this->workout->update([
            'finished_at' => now(),
        ]);

        User::tim()->addExperience(10, ExperienceType::WorkoutFinished, $this->workout->id);

        $this->dispatch('workout:finished');
    }

    public function render(): View
    {
        if ($this->workout->finished_at) {
            // Get actual performed exercises from sets, bypass the plan entirely
            $exercises = Exercise::query()->whereHas('sets', fn ($q) => $q->where('workout_id', $this->workout->id))->get();
        } else {
            // Active workout: use plan exercises
            $exercises = $this->workout->plan->exercises;
        }
        $currentExercise = $this->workout->getCurrentExercise() ?: $exercises->first();

        return view('holocron.grind.workouts.show', compact('currentExercise', 'exercises'));
    }
}
