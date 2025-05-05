<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Workouts;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Workout;
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
    }

    public function render(): View
    {
        return view('holocron.grind.workouts.show', [
            'currentExercise' => $this->workout->getCurrentExercise(),
        ]);
    }
}
