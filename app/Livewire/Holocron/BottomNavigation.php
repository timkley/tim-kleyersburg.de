<?php

namespace App\Livewire\Holocron;

use Illuminate\View\View;
use Livewire\Component;

class BottomNavigation extends Component
{
    /**
     * @var string[]
     */
    protected $listeners = [
        'workout:finished' => '$refresh',
    ];

    public function render(): View
    {
        return view('holocron.bottom-navigation');
    }
}
