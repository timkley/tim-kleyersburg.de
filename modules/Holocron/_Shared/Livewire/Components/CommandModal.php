<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Holocron\_Shared\Livewire\Traits\WithQuestCreation;

class CommandModal extends Component
{
    use WithQuestCreation;

    public function render(): View
    {
        return view('holocron::livewire.components.command-modal');
    }
}
