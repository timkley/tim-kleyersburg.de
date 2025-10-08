<?php

declare(strict_types=1);

namespace Modules\Holocron\Dashboard\Livewire\Components;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Jobs\CreateDailyGoals;
use Modules\Holocron\User\Models\DailyGoal;

class Goals extends Component
{
    public const int DAYS_TO_SHOW = 14;

    public CarbonImmutable $selectedDate;

    public function mount(): void
    {
        $this->selectedDate = CarbonImmutable::now();
    }

    public function render(): View
    {
        $goals = $this->getGoalsForPeriod(0);
        $goalsPreviousPeriod = $this->getGoalsForPeriod(self::DAYS_TO_SHOW);

        $todaysGoals = DailyGoal::whereDate('created_at', $this->selectedDate)->get();

        if ($todaysGoals->isEmpty()) {
            CreateDailyGoals::dispatchSync();
        }

        return view('holocron-dashboard::components.goals', [
            'todaysGoals' => $todaysGoals,
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

    public function trackGoal(string $type, ?int $amount): void
    {
        Validator::make(
            ['amount' => $amount],
            ['amount' => ['required', 'numeric']]
        )->validate();

        $goal = DailyGoal::for(GoalType::from($type), $this->selectedDate);
        $goal->track($amount);
    }

    /**
     * @return Collection <int, DailyGoal>
     */
    private function getGoalsForPeriod(int $days): Collection
    {
        $startDate = now()->subDays($days);
        $endDate = now()->subDays($days + self::DAYS_TO_SHOW); // Calculate the end date based on the number of days

        return DailyGoal::whereBetween('date', [$endDate, $startDate])
            ->orderBy('date')
            ->get();
    }
}
