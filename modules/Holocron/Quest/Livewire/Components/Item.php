<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\Quest\Actions\DeleteQuest;
use Modules\Holocron\Quest\Actions\PrintQuest;
use Modules\Holocron\Quest\Actions\ToggleAcceptQuest;
use Modules\Holocron\Quest\Actions\ToggleQuestComplete;
use Modules\Holocron\Quest\Models\Quest;

class Item extends Component
{
    public Quest $quest;

    public bool $showParent = true;

    public ?string $selectedDate = null;

    public function toggleComplete(): void
    {
        (new ToggleQuestComplete)->handle($this->quest);
    }

    public function toggleAccept(): void
    {
        (new ToggleAcceptQuest)->handle($this->quest);
        $this->dispatch('quest:accepted');
    }

    public function print(): void
    {
        (new PrintQuest)->handle($this->quest);
    }

    public function deleteQuest(int $id): void
    {
        (new DeleteQuest)->handle(Quest::findOrFail($id));
        $this->dispatch('quest:deleted');
        $this->skipRender();
    }

    public function render(): View
    {
        return view('holocron-quest::components.item');
    }
}
