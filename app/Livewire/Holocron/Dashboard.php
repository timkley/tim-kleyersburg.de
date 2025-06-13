<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use App\Models\Holocron\Quest;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Holocron Dashboard')]
class Dashboard extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron.dashboard', [
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
