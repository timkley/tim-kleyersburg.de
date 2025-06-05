<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Enums\Holocron\QuestStatus;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Quest;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Quests')]
class Overview extends HolocronComponent
{
    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $questDraft = '';

    public function addQuest(): void
    {
        $this->validate();

        Quest::create([
            'name' => $this->questDraft,
        ]);

        $this->reset(['questDraft']);
    }

    public function deleteQuest(int $id): void
    {
        Quest::destroy($id);
    }

    public function render(): View
    {
        return view('holocron.quests.overview', [
            'questsWithoutChildren' => Quest::query()
                ->whereNot('status', QuestStatus::Note)
                ->noChildren()
                ->notCompleted()
                ->orderByDesc('status')
                ->get(),
            'quests' => Quest::query()
                ->whereNull('quest_id')
                ->notCompleted()
                ->orderByDesc('status')->get(),
        ]);
    }
}
