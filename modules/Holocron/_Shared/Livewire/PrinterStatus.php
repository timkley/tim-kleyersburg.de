<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class PrinterStatus extends Component
{
    public function toggle(): void
    {
        auth()->user()->settings()->update(['printer_silenced' => ! auth()->user()->settings->printer_silenced]);
        auth()->user()->settings->refresh();
    }

    public function render(): View
    {
        return view('holocron::components.printer-status');
    }
}
