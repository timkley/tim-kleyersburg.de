<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Workouts;

use App\Enums\Holocron\ExperienceType;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Workout;
use App\Models\User;
use Flux\Flux;
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

    public ?int $exerciseIdToChange = null;

    public function setExercise(int $id): void
    {
        $this->workout->update([
            'current_exercise_id' => $id,
        ]);
    }

    public function swapExercise(int $newExerciseId): void
    {
        $this->workout->exercises()->where('id', $this->exerciseIdToChange)->update(['exercise_id' => $newExerciseId]);

        Flux::modal('exercise-dropdown')->close();
    }

    public function deleteExercise(): void
    {
        $this->workout->exercises()->where('id', $this->exerciseIdToChange)->delete();

        Flux::modal('exercise-dropdown')->close();
    }

    public function recordSet(): void
    {
        $this->validate();

        $this->workout->sets()->create([
            'workout_exercise_id' => $this->workout->getCurrentExercise()->id,
            'exercise_id' => $this->workout->getCurrentExercise()->exercise->id,
            'workout_id' => $this->workout->id,
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
        $workoutExercises = $this->workout->exercises;
        $currentExercise = $this->workout->getCurrentExercise() ?: $workoutExercises->first();

        return view('holocron.grind.workouts.show', [
            'workoutExercises' => $workoutExercises,
            'currentExercise' => $currentExercise,
            'availableExercises' => Exercise::all(['id', 'name']),
        ]);
    }
}
