<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class BottomNavigation extends Component
{
    #[On('workout:finished')]
    public function render(): View
    {
        return view('holocron::components.bottom-navigation');
    }
}
