<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Printer\Model\PrintQueue;

#[Title('Print Queue')]
class Index extends HolocronComponent
{
    use WithPagination;

    public function render(): View
    {
        return view('holocron-printer::index', [
            'printQueue' => PrintQueue::query()
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }
}
