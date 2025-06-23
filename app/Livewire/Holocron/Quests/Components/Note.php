<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests\Components;

use App\Models\Holocron\QuestNote;
use Illuminate\View\View;
use Livewire\Component;

class Note extends Component
{
    public QuestNote $note;

    public function render(): View
    {
        return view('holocron.quests.components.note');
    }
}
