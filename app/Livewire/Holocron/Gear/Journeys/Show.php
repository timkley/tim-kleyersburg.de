<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Gear\Journeys;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Gear\Journey;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Reise')]
class Show extends HolocronComponent
{
    public Journey $journey;

    public function render(): View
    {
        return view('holocron.gear.journeys.show');
    }
}
