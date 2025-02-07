<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use App\Enums\Holocron\Health\GoalTypes;
use App\Models\Holocron\Health\DailyGoal;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class Dashboard extends HolocronComponent
{
    public function render(): View
    {
        return view('holocron.dashboard', [
            'dailyGoals' => DailyGoal::whereDate('created_at', now())->get(),
        ]);
    }

    public function trackGoal(string $type, int $amount): void
    {
        Validator::make(['amount' => $amount], [
            'amount' => ['required', 'numeric'],
        ])->validate();

        DailyGoal::for(GoalTypes::from($type))->increment('amount', $amount);
    }
}
