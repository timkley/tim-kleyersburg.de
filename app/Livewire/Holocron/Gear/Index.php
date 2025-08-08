<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\Journey;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Gear')]
class Index extends HolocronComponent
{
    use WithJourneyCreation, WithPacklistGeneration;

    public function render(): View
    {
        return view('holocron.gear.index', [
            'journeys' => Journey::query()->whereAfterToday('ends_at')->get(),
        ]);
    }
}
