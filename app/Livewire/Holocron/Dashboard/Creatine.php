<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Dashboard;

use App\Enums\Holocron\Health\IntakeTypes;
use App\Enums\Holocron\Health\IntakeUnits;
use App\Models\Holocron\Health\Intake;
use Livewire\Component;

class Creatine extends Component
{
    public function render()
    {
        return view('holocron.dashboard.creatine', [
            'count' => Intake::where('type', IntakeTypes::Creatine)->whereDate('created_at', now())->count(),
        ]);
    }

    public function addPortion(): void
    {
        Intake::create([
            'type' => IntakeTypes::Creatine,
            'unit' => IntakeUnits::Grams,
            'amount' => 5,
        ]);
    }
}
