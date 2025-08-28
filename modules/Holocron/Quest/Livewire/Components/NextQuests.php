<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Modules\Holocron\Quest\Enums\QuestStatus;
use Modules\Holocron\Quest\Models\Quest;

class NextQuests extends Component
{
    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $questDraft = '';

    public ?int $parentQuestId = null;

    public ?string $parentQuestName = null;

    /**
     * @var string[]
     */
    protected $listeners = [
        'quest:accepted' => '$refresh',
        'quest:deleted' => '$refresh',
        'quest:created' => '$refresh',
    ];

    public function addQuest(): void
    {
        $this->validate();

        Quest::create([
            'quest_id' => $this->parentQuestId,
            'name' => $this->questDraft,
            'status' => QuestStatus::Open,
        ]);

        $this->reset();

        $this->dispatch('quest:created');
    }

    public function setParentQuest(?int $id): void
    {
        if (is_null($id)) {
            return;
        }

        $this->parentQuestId = $id;
        $this->parentQuestName = Quest::find($id)->name;

        Flux::modal('parent-search')->close();
    }

    public function render(): View
    {
        return view('holocron-quest::components.next-quests', [
            'nextQuests' => Quest::query()
                ->whereNot('status', QuestStatus::Note)
                ->noChildren()
                ->notAccepted()
                ->notCompleted()
                ->notDaily()
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
