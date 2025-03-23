<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Dashboard;

use App\Enums\Holocron\Health\GoalTypes;
use App\Models\Holocron\Health\DailyGoal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
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
        $goals = $this->getGoalsForPeriod(0);
        $goalsPreviousPeriod = $this->getGoalsForPeriod(20);

        return view('holocron.dashboard.goals', [
            'todaysGoals' => DailyGoal::whereDate('created_at', $this->selectedDate)->get(),
            'goalsPast20DaysByDay' => $goals->groupBy('date'),
            'goalsPast20DaysCount' => $goals->count(),
            'goalsPast20DaysReachedCount' => $goals->sum('reached'),
            'goalsPast40DaysCount' => $goalsPreviousPeriod->count(),
            'goalsPast40DaysReachedCount' => $goalsPreviousPeriod->sum('reached'),
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

    private function getGoalsForPeriod(int $days): Collection
    {
        $startDate = now()->subDays($days);
        $endDate = now()->subDays($days + 20); // Calculate the end date based on the number of days

        return DailyGoal::whereBetween('date', [$endDate, $startDate])
            ->orderBy('date')
            ->get();
    }
}
