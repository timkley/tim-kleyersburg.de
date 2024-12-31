<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Dashboard;

use App\Enums\Holocron\Health\IntakeTypes;
use App\Enums\Holocron\Health\IntakeUnits;
use App\Models\Holocron\Health\Intake;
use App\Services\WaterService;
use Livewire\Component;

class Water extends Component
{
    public function render()
    {
        return view('holocron.dashboard.water', [
            'waterIntake' => WaterService::todaysIntake(),
            'remainingWater' => WaterService::remaining(),
        ]);
    }

    public function addBottle(): void
    {
        Intake::create([
            'type' => IntakeTypes::Water,
            'amount' => 500,
            'unit' => IntakeUnits::Milliliters,
        ]);
    }
}
