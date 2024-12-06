<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use Illuminate\View\View;

class Dashboard extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron.dashboard');
    }
}
