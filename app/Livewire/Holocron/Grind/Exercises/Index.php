<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Grind\Exercises;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Grind\Exercise;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Übungen')]
class Index extends HolocronComponent
{
    #[Validate('required|min:3|max:255')]
    public ?string $name = null;

    public ?string $description = null;

    public ?string $instructions = null;

    public function submit(): void
    {
        $validated = $this->validate();

        Exercise::create($validated);

        $this->reset('name');
    }

    public function delete(int $id): void
    {
        Exercise::destroy($id);

        Flux::toast('Übung gelöscht');
    }

    public function render(): View
    {
        return view('holocron.grind.exercises.index', [
            'exercises' => Exercise::all(),
        ]);
    }
}
