<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Components;

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
        return view('holocron.components.bottom-navigation');
    }
}
