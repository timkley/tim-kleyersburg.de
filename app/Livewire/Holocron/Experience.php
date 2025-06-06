<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Erfahrungspunkte')]
class Experience extends HolocronComponent
{
    use WithPagination;

    public function render(): View
    {
        return view('holocron.experience', [
            'experiences' => auth()->user()->experienceLogs()->paginate(10),
        ]);
    }
}
