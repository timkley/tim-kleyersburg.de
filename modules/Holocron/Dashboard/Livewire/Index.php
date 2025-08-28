<?php

declare(strict_types=1);

namespace Modules\Holocron\Dashboard\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Quest\Models\Quest;

#[Title('Holocron Dashboard')]
class Index extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron-dashboard::index', [
            'todaysQuests' => Quest::query()
                ->today()
                ->notCompleted()
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }
}
