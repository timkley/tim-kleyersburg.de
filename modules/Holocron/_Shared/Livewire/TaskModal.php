<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Modules\Holocron\Quest\Models\Quest;

class TaskModal extends Component
{
    #[Validate('required')]
    public string $name = '';

    #[Validate('required')]
    public bool $should_be_printed = false;

    #[Validate('date')]
    public string $date = '';

    public bool $hasIntent = false;

    public function submit(bool $andNew = false): void
    {
        $validated = $this->validate();

        $quest = Quest::create([
            'name' => $this->name,
            ...$validated,
        ]);

        $this->reset();

        if ($andNew) {
            $this->dispatch('quest:created');

            return;
        }

        $this->redirect(route('holocron.quests.show', $quest));
    }

    public function render(): View
    {
        return view('holocron::livewire.components.task-modal');
    }
}
