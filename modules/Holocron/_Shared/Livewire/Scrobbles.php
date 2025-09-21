<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use App\Models\Scrobble;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Chopper')]
class Scrobbles extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron-dashboard::scrobbles', [
            'scrobbles' => Scrobble::query()->limit(100)->latest('played_at')->get(),
            'count' => Scrobble::query()->count(),
        ]);
    }
}
