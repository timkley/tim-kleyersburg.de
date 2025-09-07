<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire\Components;

use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Modules\Holocron\Quest\Enums\QuestStatus;
use Modules\Holocron\Quest\Models\Quest;

class MainQuests extends Component
{
    #[Url]
    public ?string $query = null;

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
        $notes = Quest::query()
            ->whereNull('quest_id')
            ->where('status', '=', QuestStatus::Note)
            ->orderBy('name')
            ->get();

        return view('holocron-quest::components.main-quests', [
            'notes' => $notes,
            'searchResults' => $this->query ? Quest::search($this->query)->get() : null,
        ]);
    }
}
