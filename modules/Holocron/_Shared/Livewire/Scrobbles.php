<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use App\Models\Scrobble;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

#[Title('Scrobbles')]
class Scrobbles extends HolocronComponent
{
    use WithPagination;

    public function render(): View
    {
        return view('holocron-dashboard::scrobbles', [
            'scrobbles' => Scrobble::query()->latest('played_at')->paginate(100),
            'count' => Scrobble::query()->count(),
        ]);
    }
}
