<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use App\Enums\Holocron\Health\GoalTypes;
use App\Models\Holocron\Health\DailyGoal;
use Illuminate\View\View;

class Water extends HolocronComponent
{
    public ?float $weight = null;

    public ?int $intake = null;

    public function render(): View
    {
        $dailyGoal = DailyGoal::for(GoalTypes::Water);
        $goal = $dailyGoal->goal;
        $amount = $dailyGoal->amount;
        $remainingWater = $goal - $amount;

        return view('holocron.water', compact('goal', 'amount', 'remainingWater'));
    }

    public function addWaterIntake(): void
    {
        $this->validate([
            'intake' => ['required', 'numeric'],
        ]);

        DailyGoal::for(GoalTypes::Water)->increment('amount', $this->intake);

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
