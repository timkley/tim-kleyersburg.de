<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\Quest\Models\Note as NoteModel;

class Note extends Component
{
    public NoteModel $note;

    public function render(): View
    {
        return view('holocron-quest::components.note');
    }
}
