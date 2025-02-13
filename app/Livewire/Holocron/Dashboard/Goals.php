<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Dashboard;

use App\Enums\Holocron\Health\GoalTypes;
use App\Models\Holocron\Health\DailyGoal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Component;

class Goals extends Component
{
    public CarbonImmutable $selectedDate;

    public function mount(): void
    {
        $this->selectedDate = CarbonImmutable::now();
    }

    public function render(): View
    {
        return view('holocron.dashboard.goals', [
            'dailyGoals' => DailyGoal::whereDate('created_at', $this->selectedDate)->get(),
            'goalsByDay' => DailyGoal::whereDate('date', '>', now()->subDays(20))->get()->groupBy('date'),
        ]);
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = CarbonImmutable::parse($date);
    }

    public function trackGoal(string $type, int $amount): void
    {
        Validator::make(
            ['amount' => $amount],
            ['amount' => ['required', 'numeric']]
        )->validate();

        DailyGoal::for(GoalTypes::from($type), $this->selectedDate)->increment('amount', $amount);
    }
}
