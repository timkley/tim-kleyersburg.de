<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests\Components;

use App\Enums\Holocron\QuestStatus;
use App\Models\Holocron\Quest\Quest;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

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
    }

    public function setParentQuest(?int $id): void
    {
        $this->parentQuestId = $id;
        $this->parentQuestName = Quest::find($id)->name;

        Flux::modal('parent-search')->close();
    }

    public function render(): View
    {
        [$tasks, $notes] = Quest::query()
            ->whereNull('quest_id')
            ->notCompleted()
            ->orderBy('name')
            ->get()
            ->partition(fn (Quest $quest) => $quest->status !== QuestStatus::Note);

        return view('holocron.quests.components.main-quests', [
            'tasks' => $tasks,
            'notes' => $notes,
            'searchResults' => $this->query ? Quest::search($this->query)->get() : null,
        ]);
    }
}
