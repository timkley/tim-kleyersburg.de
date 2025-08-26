<?php

declare(strict_types=1);

namespace Modules\Holocron\Dashboard\Livewire\Components;

use App\Services\Nasa;
use Illuminate\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Apod extends Component
{
    public function render(): View
    {
        $apod = Nasa::apod();

        return view('holocron-dashboard::components.apod', [
            'apod' => $apod,
        ]);
    }

    public function placeholder(): View
    {
        return view('holocron-dashboard::components.apod');
    }
}
