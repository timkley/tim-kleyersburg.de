<?php

declare(strict_types=1);

namespace Modules\Holocron\User\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;

#[Title('Erfahrungspunkte')]
class Experience extends HolocronComponent
{
    use WithPagination;

    public function render(): View
    {
        return view('holocron-user::experience', [
            'experiences' => auth()->user()->experienceLogs()->latest()->paginate(10),
        ]);
    }
}
