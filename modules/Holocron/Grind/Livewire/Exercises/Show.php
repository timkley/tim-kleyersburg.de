<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Exercises;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Exercise;

class Show extends HolocronComponent
{
    public Exercise $exercise;

    public ExerciseForm $form;

    public function mount(Exercise $exercise): void
    {
        $this->exercise = $exercise;
        $this->form->setExercise($exercise);
    }

    public function updated(string $property, mixed $value): void
    {
        if (! str_starts_with($property, 'form.')) {
            return;
        }

        $formProperty = str_replace('form.', '', $property);

        if (! in_array($formProperty, ['name', 'description', 'instructions'])) {
            return;
        }

        $this->form->save($formProperty, $value);
    }

    public function render(): View
    {
        /** @var Collection<int, array{date: string, total_volume: float|int}> $data */
        $data = $this->exercise->volumePerWorkout()
            ->map(fn ($item): array => [
                'date' => Carbon::parse($item->workout_finished_at)->format('Y-m-d'),
                'total_volume' => (float) $item->total_volume,
            ])
            ->values();

        return view('holocron-grind::exercises.show', [
            'data' => $data->toArray(),
        ])->title($this->exercise->name);
    }
}
