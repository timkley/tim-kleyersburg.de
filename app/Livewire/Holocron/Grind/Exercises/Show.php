<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Exercises;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Set;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Show extends HolocronComponent
{
    public Exercise $exercise;

    public ?string $description;

    public function updated(string $property, mixed $value): void
    {
        $this->exercise->update([
            $property => $value,
        ]);
    }

    public function mount(Exercise $exercise): void
    {
        $this->fill($exercise);
    }

    public function render(): View
    {
        /** @var Collection<int, array{date: string, total_volume: float|int}> $data */
        $data = $this->exercise->volumePerWorkout()
            ->map(fn (Set $set): array => [
                'date' => $set->workoutExercise->workout->finished_at,
                'total_volume' => (float) $set->total_volume,
            ])
            ->values();

        return view('holocron.grind.exercises.show', [
            'data' => $data->toArray(),
        ]);
    }

    public function rendering(View $view): void
    {
        $view->title($this->exercise->name);
    }
}
