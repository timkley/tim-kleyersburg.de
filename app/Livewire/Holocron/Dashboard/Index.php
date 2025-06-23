<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Dashboard;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Quest;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Holocron Dashboard')]
class Index extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron.dashboard.index', [
            'accepted_quests' => Quest::query()
                ->accepted()
                ->notCompleted()
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
