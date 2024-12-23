<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use App\Models\WaterIntake;
use App\Services\WaterService;
use Illuminate\View\View;

class Water extends HolocronComponent
{
    public ?float $weight = null;

    public ?int $intake = null;

    public function render(): View
    {
        $goal = WaterService::goal();
        $waterIntake = WaterService::dailyIntake();
        $remaining = WaterService::remaining();
        $percentage = WaterService::percentage();

        return view('holocron.water', compact('goal', 'waterIntake', 'remaining', 'percentage'));
    }

    public function addWaterIntake(): void
    {
        $this->validate([
            'intake' => ['required', 'numeric'],
        ]);

        WaterIntake::create([
            'amount' => $this->intake,
        ]);

        $this->reset('intake');
    }

    public function setWeight(): void
    {
        $this->validate([
            'weight' => ['required', 'numeric'],
        ]);

        auth()->user()->settings()->updateOrCreate([
            'weight' => $this->weight,
        ]);
    }
}
