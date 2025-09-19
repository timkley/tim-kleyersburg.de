<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\Quest\Models\Quest;

class NextQuests extends Component
{
    /**
     * @var string[]
     */
    protected $listeners = [
        'quest:accepted' => '$refresh',
        'quest:deleted' => '$refresh',
        'quest:created' => '$refresh',
    ];

    public function render(): View
    {
        return view('holocron-quest::components.next-quests', [
            'nextQuests' => Quest::query()
                ->areNotNotes()
                ->noChildren()
                ->notToday()
                ->notCompleted()
                ->notDaily()
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
