<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Quest\Models\QuestRecurrence;

#[Title('Recurring Quests')]
class RecurringQuests extends HolocronComponent
{
    public function render(): View
    {
        $recurringQuests = QuestRecurrence::query()
            ->with('quest')
            ->orderBy('every_x_days')
            ->get();

        return view('holocron-quest::recurring-quests', [
            'recurringQuests' => $recurringQuests,
        ]);
    }
}
