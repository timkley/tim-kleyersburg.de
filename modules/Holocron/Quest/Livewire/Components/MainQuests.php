<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Modules\Holocron\Quest\Enums\QuestStatus;
use Modules\Holocron\Quest\Models\Quest;

class MainQuests extends Component
{
    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $questDraft = '';

    #[Url]
    public ?string $query = null;

    public ?int $parentQuestId = null;

    public ?string $parentQuestName = null;

    /**
     * @var string[]
     */
    protected $listeners = [
        'quest:accepted' => '$refresh',
        'quest:deleted' => '$refresh',
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
        [$tasks, $notes] = Quest::query()
            ->whereNull('quest_id')
            ->notCompleted()
            ->notDaily()
            ->orderBy('name')
            ->get()
            ->partition(fn (Quest $quest) => $quest->status !== QuestStatus::Note);

        return view('holocron-quest::components.main-quests', [
            'tasks' => $tasks,
            'notes' => $notes,
            'searchResults' => $this->query ? Quest::search($this->query)->get() : null,
        ]);
    }
}
