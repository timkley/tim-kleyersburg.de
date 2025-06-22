<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Enums\Holocron\QuestStatus;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Quest;
use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;

#[Title('Quests')]
class Overview extends HolocronComponent
{
    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $questDraft = '';

    public ?int $parentQuestId = null;

    public ?string $parentQuestName = null;

    #[Url]
    public ?string $query = null;

    /**
     * @var string[]
     */
    protected $listeners = [
        'quest:accepted' => '$refresh',
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

    public function deleteQuest(int $id): void
    {
        Quest::destroy($id);
    }

    public function setParentQuest(?int $id): void
    {
        $this->parentQuestId = $id;
        $this->parentQuestName = Quest::find($id)->name;

        Flux::modal('parent-search')->close();
    }

    public function render(): View
    {
        return view('holocron.quests.overview', [
            'acceptedQuests' => Quest::query()
                ->accepted()
                ->notCompleted()
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->get(),
            'questsWithoutChildren' => Quest::query()
                ->whereNot('status', QuestStatus::Note)
                ->noChildren()
                ->notAccepted()
                ->notCompleted()
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->get(),
            'quests' => Quest::query()
                ->whereNull('quest_id')
                ->notCompleted()
                ->orderBy('name')
                ->get(),
            'searchResults' => $this->query ? Quest::search($this->query)->get() : null,
        ]);
    }
}
