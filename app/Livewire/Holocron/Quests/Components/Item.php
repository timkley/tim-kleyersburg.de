<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests\Components;

use App\Enums\Holocron\QuestStatus;
use App\Models\Holocron\Quest;
use Illuminate\View\View;
use Livewire\Component;

class Item extends Component
{
    public Quest $quest;

    public bool $showParent = true;

    public function setStatus(string $status): void
    {
        $this->quest->setStatus(QuestStatus::from($status));
    }

    public function toggleAccept(): void
    {
        $this->quest->update(['accepted' => ! $this->quest->accepted]);
        $this->dispatch('quest:accepted');
    }

    public function render(): View
    {
        return view('holocron.quests.components.item');
    }
}
