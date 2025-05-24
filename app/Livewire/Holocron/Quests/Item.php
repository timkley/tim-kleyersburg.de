<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Models\Holocron\Quest;
use Illuminate\View\View;
use Livewire\Component;

class Item extends Component
{
    public Quest $quest;

    public function setStatus(string $status): void
    {
        $this->quest->update([
            'status' => $status,
        ]);
    }

    public function render(): View
    {
        return view('holocron.quests.item');
    }
}
