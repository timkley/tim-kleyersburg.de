<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\Quest\Models\Quest;

class TodaysQuests extends Component
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
        return view('holocron-quest::components.todays-quests', [
            'todaysQuests' => Quest::query()
                ->today()
                ->notCompleted()
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
