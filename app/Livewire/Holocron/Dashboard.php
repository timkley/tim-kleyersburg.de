<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Holocron Dashboard')]
class Dashboard extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron.dashboard');
    }
}
