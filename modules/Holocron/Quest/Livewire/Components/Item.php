<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\Printer\Services\Printer;
use Modules\Holocron\Quest\Models\Quest;

class Item extends Component
{
    public Quest $quest;

    public bool $showParent = true;

    public ?string $selectedDate = null;

    public function toggleComplete(): void
    {
        if ($this->quest->isCompleted()) {
            $this->quest->update(['completed_at' => null]);
        } else {
            $this->quest->complete();
        }
    }

    public function toggleAccept(): void
    {
        $this->quest->update(['date' => ! $this->quest->date ? now() : null]);
        $this->dispatch('quest:accepted');
    }

    public function print(): void
    {
        $this->quest->update([
            'should_be_printed' => true,
        ]);

        Printer::print('holocron-quest::print-view', ['quest' => $this->quest], [
            route('holocron.quests.complete', [$this->quest]),
        ]);
    }

    public function deleteQuest(int $id): void
    {
        Quest::destroy($id);
        $this->dispatch('quest:deleted');
    }

    public function render(): View
    {
        return view('holocron-quest::components.item');
    }
}
