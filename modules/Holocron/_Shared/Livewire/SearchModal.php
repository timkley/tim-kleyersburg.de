<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Holocron\Quest\Models\Quest;

class SearchModal extends Component
{
    public string $query = '';

    public bool $includeCompleted = false;

    public function render(): View
    {
        return view('holocron::livewire.components.search-modal', [
            'results' => $this->query ? Quest::search($this->query)->options(['filter_by' => $this->includeCompleted ? '' : 'completed_at:<=0'])->get() : null,
        ]);
    }
}
