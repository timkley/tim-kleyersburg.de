<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Enums\Holocron\QuestStatus;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Quest;
use App\Models\Holocron\QuestNote;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;

#[Title('Quests')]
class Overview extends HolocronComponent
{
    public ?Quest $quest = null;

    #[Rule('required')]
    #[Rule('min:3')]
    #[Rule('max:255')]
    public ?string $name = '';

    public ?string $description = '';

    public QuestStatus $status;

    #[Rule('required')]
    #[Rule('min:3')]
    #[Rule('max:255')]
    public string $questDraft = '';

    #[Rule('required')]
    #[Rule('min:3')]
    public string $noteDraft = '';

    public function updating($property, $value): void
    {
        $this->validateOnly($property);

        $this->quest->update([
            $property => $value,
        ]);
    }

    public function addQuest(): void
    {
        $this->validateOnly('questDraft');

        Quest::create([
            'quest_id' => $this->quest?->id,
            'name' => $this->questDraft,
        ]);

        $this->reset(['questDraft']);
    }

    public function deleteQuest(int $id): void
    {
        Quest::destroy($id);
    }

    public function addNote(): void
    {
        $this->validateOnly('noteDraft');

        $this->quest->notes()->create([
            'content' => $this->noteDraft,
        ]);

        $this->reset(['noteDraft']);
    }

    public function deleteNote(int $id): void
    {
        QuestNote::destroy($id);
    }

    public function mount(?Quest $quest): void
    {
        $this->quest = $quest ?? new Quest;
        $this->name = $quest->name;
        $this->description = $quest->description;
        $this->status = $quest->status ?? QuestStatus::Open;
    }

    public function render(): View
    {
        return view('holocron.quests.overview', [
            'breadcrumb' => $this->quest->getBreadcrumb(),
        ]);
    }

    public function rendering(View $view): void
    {
        $view->title($this->quest->name ?? 'Quests');
    }
}
