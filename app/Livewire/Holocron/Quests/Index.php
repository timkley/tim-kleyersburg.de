<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Livewire\Holocron\HolocronComponent;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Quests')]
class Index extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron.quests.index');
    }
}
