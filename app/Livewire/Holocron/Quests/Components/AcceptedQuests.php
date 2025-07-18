<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests\Components;

use App\Models\Holocron\Quest\Quest;
use Illuminate\View\View;
use Livewire\Component;

class AcceptedQuests extends Component
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
        return view('holocron.quests.components.accepted-quests', [
            'acceptedQuests' => Quest::query()
                ->accepted()
                ->notCompleted()
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
