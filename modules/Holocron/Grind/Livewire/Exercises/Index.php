<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Exercises;

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Exercise;

#[Title('Übungen')]
class Index extends HolocronComponent
{
    public function delete(int $id): void
    {
        Exercise::destroy($id);

        Flux::toast('Übung gelöscht');
    }

    #[On('exercise-created')]
    public function render(): View
    {
        return view('holocron-grind::exercises.index', [
            'exercises' => Exercise::all(),
        ]);
    }
}
