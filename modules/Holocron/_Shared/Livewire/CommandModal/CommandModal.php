<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\CommandModal;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Holocron\_Shared\Livewire\CommandModal\Traits\WithQuestCreation;

class CommandModal extends Component
{
    use WithQuestCreation;

    public bool $hasIntent = false;

    public function render(): View
    {
        return view('holocron::livewire.components.command-modal');
    }
}
