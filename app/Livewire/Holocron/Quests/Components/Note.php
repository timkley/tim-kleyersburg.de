<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests\Components;

use App\Models\Holocron\Quest\Note as NoteModel;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Note extends Component
{
    public NoteModel $note;

    #[On('note-updated.{note.id}')]
    public function refresh(): void
    {
        $this->note->refresh();
    }

    public function render(): View
    {
        return view('holocron.quests.components.note');
    }
}
