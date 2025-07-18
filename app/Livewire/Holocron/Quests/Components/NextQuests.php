<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests\Components;

use App\Enums\Holocron\QuestStatus;
use App\Models\Holocron\Quest\Quest;
use Illuminate\View\View;
use Livewire\Component;

class NextQuests extends Component
{
    /**
     * @var string[]
     */
    protected $listeners = [
        'quest:accepted' => '$refresh',
        'quest:deleted' => '$refresh',
    ];

    public function render(): View
    {
        return view('holocron.quests.components.next-quests', [
            'nextQuests' => Quest::query()
                ->whereNot('status', QuestStatus::Note)
                ->noChildren()
                ->notAccepted()
                ->notCompleted()
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
