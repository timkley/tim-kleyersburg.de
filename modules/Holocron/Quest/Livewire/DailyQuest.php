<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Quest\Models\Quest;

#[Title('Daily Quest')]
class DailyQuest extends HolocronComponent
{
    #[Url(as: 'date')]
    public string $date = '';

    #[Computed]
    public function currentDate(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->date);
    }

    public function mount(): void
    {
        if (! $this->date) {
            $this->date = today()->format('Y-m-d');
        }
    }

    public function nextDay(): void
    {
        $this->date = $this->currentDate()->addDay()->format('Y-m-d');
    }

    public function previousDay(): void
    {
        $this->date = $this->currentDate()->subDay()->format('Y-m-d');
    }

    public function goToToday(): void
    {
        $this->date = today()->format('Y-m-d');
    }

    public function render(): View
    {
        $quest = Quest::query()->firstOrCreate(
            [
                'date' => $this->currentDate()->toDateString(),
                'daily' => true,
            ],
            ['name' => $this->currentDate()->format('d. F Y')]
        )->refresh();

        return view('holocron-quest::daily-quest', [
            'quest' => $quest,
        ]);
    }
}
