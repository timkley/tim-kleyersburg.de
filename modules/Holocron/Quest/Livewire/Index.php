<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Quest\Models\Quest;

#[Title('Quests')]
class Index extends HolocronComponent
{
    public ?Quest $quest = null;

    public CarbonImmutable $currentDate;

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->currentDate = today()->toImmutable();
        $this->loadDailyQuest();
    }

    public function loadDailyQuest(): void
    {
        $this->quest = Quest::firstOrCreate(
            ['date' => $this->currentDate->toDateString()],
            ['name' => $this->currentDate->toFormattedDateString()]
        );
    }

    public function nextDay(): void
    {
        $this->currentDate = $this->currentDate->addDay();
        $this->loadDailyQuest();
    }

    public function previousDay(): void
    {
        $this->currentDate = $this->currentDate->subDay();
        $this->loadDailyQuest();
    }

    public function goToToday(): void
    {
        $this->currentDate = today()->toImmutable();
        $this->loadDailyQuest();
    }

    public function goToDate(string $isoDate): void
    {
        $this->currentDate = CarbonImmutable::parse($isoDate);
        $this->loadDailyQuest();
    }

    public function rescheduleSubQuest(int $subQuestId, string $newIsoDate): void
    {
        $targetQuest = Quest::firstOrCreate(
            ['date' => CarbonImmutable::parse($newIsoDate)->toDateString()],
            ['name' => CarbonImmutable::parse($newIsoDate)->toFormattedDateString()]
        );

        $subQuest = Quest::find($subQuestId);
        $subQuest?->update(['quest_id' => $targetQuest->id]);

        $this->dispatch('quest:rescheduled');
    }

    public function updatedCurrentDate(): void
    {
        $this->loadDailyQuest();
    }

    public function render(): View
    {
        $subQuests = Quest::where('quest_id', $this->quest?->id)
            ->orWhere(function ($query) {
                $query->whereHas('parent', function ($query) {
                    $query->where('date', '<', $this->currentDate->toDateString());
                })->where('status', '!=', 'completed');
            })
            ->get();

        return view('holocron-quest::index', [
            'subQuests' => $subQuests,
        ]);
    }
}
