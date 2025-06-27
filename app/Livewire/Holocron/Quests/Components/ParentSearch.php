<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests\Components;

use App\Models\Holocron\Quest\Quest;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class ParentSearch extends Component
{
    public string $searchTerm = '';

    /** @var array<mixed>|Collection<int, Quest> */
    public $quests = [];

    public function updatingSearchTerm(mixed $value): void
    {
        if (empty($value)) {
            $this->quests = [];

            return;
        }

        $this->quests = Quest::search($value)->take(10)->get();
    }

    public function render(): View
    {
        return view('holocron.quests.components.parent-search');
    }
}
