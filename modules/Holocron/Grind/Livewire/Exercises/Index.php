<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Exercises;

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\Exercise;

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
        Flux::modal('new')->close();
    }

    public function delete(int $id): void
    {
        Exercise::destroy($id);

        Flux::toast('Übung gelöscht');
    }

    public function render(): View
    {
        return view('holocron-grind::exercises.index', [
            'exercises' => Exercise::all(),
        ]);
    }
}
