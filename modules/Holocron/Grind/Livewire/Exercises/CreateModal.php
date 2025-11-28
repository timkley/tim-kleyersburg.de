<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Exercises;

use Flux\Flux;
use Illuminate\View\View;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;

class CreateModal extends HolocronComponent
{
    public ExerciseForm $form;

    public function submit(): void
    {
        $this->form->store();

        Flux::modal('new-exercise')->close();
        Flux::toast('Übung erfolgreich erstellt.', variant: 'success');
        $this->dispatch('exercise-created');
    }

    public function render(): View
    {
        return view('holocron-grind::exercises.create-modal');
    }
}
