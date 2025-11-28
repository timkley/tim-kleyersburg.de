<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Exercises;

use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\Holocron\Grind\Models\Exercise;

class ExerciseForm extends Form
{
    public ?Exercise $exercise = null;

    #[Validate('required|min:3|max:255')]
    public string $name = '';

    #[Validate('nullable|max:255')]
    public ?string $description = null;

    #[Validate('nullable|max:255')]
    public ?string $instructions = null;

    public function setExercise(Exercise $exercise): void
    {
        $this->exercise = $exercise;
        $this->name = $exercise->name;
        $this->description = $exercise->description;
        $this->instructions = $exercise->instructions;
    }

    public function store(): void
    {
        $this->validate();

        Exercise::create($this->only(['name', 'description', 'instructions']));

        $this->reset();
    }

    public function save(string $property, mixed $value): void
    {
        $this->validateOnly($property);

        $this->exercise->update([$property => $value]);

        $this->exercise->refresh();
    }
}
