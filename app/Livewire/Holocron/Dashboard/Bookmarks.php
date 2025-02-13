<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Dashboard;

use Illuminate\View\View;
use Livewire\Component;

class Bookmarks extends Component
{
    public function render(): View
    {
        return view('holocron.dashboard.bookmarks');
    }
}
