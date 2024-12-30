<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use App\Services\WaterService;
use Illuminate\View\View;

class Dashboard extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron.dashboard', [
            'waterIntake' => WaterService::todaysIntake(),
            'remainingWater' => WaterService::remaining(),
        ]);
    }
}
