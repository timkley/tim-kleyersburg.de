<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Services\TemporalAwarenessService;

#[Title('Daily Quests')]
class DailyQuests extends HolocronComponent
{
    #[Url]
    public CarbonImmutable $selectedDate;

    #[Validate('required')]
    #[Validate('min:3')]
    #[Validate('max:255')]
    public string $questDraft = '';

    public function mount(): void
    {
        $this->selectedDate = CarbonImmutable::now();
    }

    public function addQuest(): void
    {
        $this->validateOnly('questDraft');

        // Use temporal awareness to detect date references in quest content
        $temporalService = new TemporalAwarenessService();
        $suggestedDate = $temporalService->extractPrimaryDate($this->questDraft);

        // Use detected date or fall back to selected date
        $questDate = $suggestedDate
            ? $suggestedDate->format('Y-m-d')
            : $this->selectedDate;

        Quest::create([
            'name' => $this->questDraft,
            'quest_date' => $questDate,
        ]);

        $this->reset('questDraft');

        // Update selected date if temporal awareness detected a different date
        if ($suggestedDate && $suggestedDate->format('Y-m-d') !== $this->selectedDate) {
            $this->selectedDate = $suggestedDate->format('Y-m-d');
        }
    }

    public function setQuestDate(string $date): void
    {
        $this->selectedDate = CarbonImmutable::parse($date);
    }

    public function previousDay(): void
    {
        $this->selectedDate = CarbonImmutable::parse($this->selectedDate)->subDay();
    }

    public function nextDay(): void
    {
        $this->selectedDate = CarbonImmutable::parse($this->selectedDate)->addDay();
    }

    public function today(): void
    {
        $this->selectedDate = CarbonImmutable::now();
    }

    public function toggleQuestStatus(int $questId): void
    {
        $quest = Quest::findOrFail($questId);

        $newStatus = $quest->status->value === 'complete'
            ? \Modules\Holocron\Quest\Enums\QuestStatus::Open
            : \Modules\Holocron\Quest\Enums\QuestStatus::Complete;

        $quest->setStatus($newStatus);
    }

    public function render(): View
    {
        // Ensure selectedDate is not empty
        if (empty($this->selectedDate)) {
            $this->selectedDate = CarbonImmutable::now();
        }

        $quests = Quest::query()
            ->whereDate('quest_date', $this->selectedDate->format('Y-m-d'))
            ->orderBy('created_at')
            ->get();

        return view('holocron-quest::daily-quests', [
            'quests' => $quests,
            'selectedDate' => Carbon::parse($this->selectedDate),
        ]);
    }
}
