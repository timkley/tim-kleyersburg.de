<?php

declare(strict_types=1);

namespace Modules\Holocron\Gear\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Gear\Models\Journey;

#[Title('Gear')]
class Index extends HolocronComponent
{
    use WithJourneyCreation, WithPacklistGeneration;

    public function render(): View
    {
        return view('holocron-gear::index', [
            'journeys' => Journey::query()->where('ends_at', '>=', today()->toDateString())->get(),
        ]);
    }
}
