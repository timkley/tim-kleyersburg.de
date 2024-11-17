<?php

namespace App\Livewire\Holocron;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.holocron')]
class Dashboard extends Component
{
    public function render()
    {
        return view('holocron.dashboard');
    }
}
