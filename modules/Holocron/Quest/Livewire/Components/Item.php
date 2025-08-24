<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Carbon\CarbonImmutable;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\Quest\Enums\QuestStatus;
use Modules\Holocron\Quest\Models\Quest;

class Item extends Component
{
    public Quest $quest;

    public bool $showParent = true;

    public ?string $selectedDate = null;

    public function setStatus(string $status): void
    {
        $this->quest->setStatus(QuestStatus::from($status));
    }

    public function toggleAccept(): void
    {
        $this->quest->update(['accepted' => ! $this->quest->accepted]);
        $this->dispatch('quest:accepted');
    }

    public function deleteQuest(int $id): void
    {
        Quest::destroy($id);
        $this->dispatch('quest:deleted');
    }

    public function reschedule(): void
    {
        $targetQuest = Quest::firstOrCreate(
            ['date' => CarbonImmutable::parse($this->selectedDate)->toDateString()],
            ['name' => CarbonImmutable::parse($this->selectedDate)->toFormattedDateString()]
        );

        $this->quest->update(['quest_id' => $targetQuest->id]);

        $this->dispatch('quest:rescheduled');
    }

    public function render(): View
    {
        return view('holocron-quest::components.item');
    }
}
