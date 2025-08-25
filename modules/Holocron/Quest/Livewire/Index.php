<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;

#[Title('Quests')]
class Index extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron-quest::index');
    }
}
