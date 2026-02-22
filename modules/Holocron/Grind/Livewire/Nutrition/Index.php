<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Livewire\Nutrition;

use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\User;

#[Title('Ernährung')]
class Index extends HolocronComponent
{
    public string $date;

    public string $dayType = 'rest';

    public string $mealName = '';

    public ?string $mealTime = null;

    public ?int $mealKcal = null;

    public ?int $mealProtein = null;

    public ?int $mealFat = null;

    public ?int $mealCarbs = null;

    public int $averageKcal = 0;

    public int $averageProtein = 0;

    public int $averageFat = 0;

    public int $averageCarbs = 0;

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->loadDayType();
        $this->calculateAverages();
    }

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->format('Y-m-d');
        $this->loadDayType();
        $this->calculateAverages();
    }

    public function nextDay(): void
    {
        $this->date = Carbon::parse($this->date)->addDay()->format('Y-m-d');
        $this->loadDayType();
        $this->calculateAverages();
    }

    public function goToDate(string $date): void
    {
        $this->date = $date;
        $this->loadDayType();
        $this->calculateAverages();
    }

    public function updatedDayType(string $type): void
    {
        $day = $this->getOrCreateDay();
        $day->update(['type' => $type]);
    }

    public function setTrainingLabel(string $label): void
    {
        $day = $this->getOrCreateDay();
        $day->update(['training_label' => $label]);
    }

    public function updateNotes(string $notes): void
    {
        $day = $this->getOrCreateDay();
        $day->update(['notes' => $notes]);
    }

    public function addMeal(): void
    {
        $this->validate([
            'mealName' => 'required|string',
            'mealKcal' => 'required|integer|min:0',
            'mealProtein' => 'required|integer|min:0',
            'mealFat' => 'required|integer|min:0',
            'mealCarbs' => 'required|integer|min:0',
        ]);

        $day = $this->getOrCreateDay();

        $meal = array_filter([
            'name' => $this->mealName,
            'time' => $this->mealTime,
            'kcal' => $this->mealKcal,
            'protein' => $this->mealProtein,
            'fat' => $this->mealFat,
            'carbs' => $this->mealCarbs,
        ], fn ($value) => $value !== null && $value !== '');

        $meals = $day->meals ?? [];
        $meals[] = $meal;
        $day->update(['meals' => $meals]);
        $day->recalculateTotals();

        $this->reset('mealName', 'mealTime', 'mealKcal', 'mealProtein', 'mealFat', 'mealCarbs');
        $this->calculateAverages();
    }

    public function deleteMeal(int $index): void
    {
        $day = $this->getOrCreateDay();

        $meals = $day->meals ?? [];
        array_splice($meals, $index, 1);
        $meals = array_values($meals);

        $day->update(['meals' => $meals]);
        $day->recalculateTotals();
        $this->calculateAverages();
    }

    public function render(): View
    {
        $day = NutritionDay::query()->whereDate('date', $this->date)->first();

        /** @var User $user */
        $user = auth()->user();
        $allTargets = $user->settings?->nutrition_daily_targets;
        $dayType = $day !== null ? $day->type : 'rest';
        $targets = $allTargets[$dayType] ?? null;

        return view('holocron-grind::nutrition.index', [
            'day' => $day,
            'targets' => $targets,
        ]);
    }

    private function loadDayType(): void
    {
        $day = NutritionDay::query()->whereDate('date', $this->date)->first();
        $this->dayType = $day !== null ? $day->type : 'rest';
    }

    private function getOrCreateDay(): NutritionDay
    {
        $day = NutritionDay::query()->whereDate('date', $this->date)->first();

        if ($day) {
            return $day;
        }

        return NutritionDay::query()->create([
            'date' => Carbon::parse($this->date),
            'type' => 'rest',
            'meals' => [],
            'total_kcal' => 0,
            'total_protein' => 0,
            'total_fat' => 0,
            'total_carbs' => 0,
        ]);
    }

    private function calculateAverages(): void
    {
        $days = NutritionDay::query()
            ->whereDate('date', '<=', $this->date)
            ->whereDate('date', '>=', Carbon::parse($this->date)->subDays(6)->format('Y-m-d'))
            ->get();

        $count = max(1, $days->count());

        $this->averageKcal = (int) round($days->sum('total_kcal') / $count);
        $this->averageProtein = (int) round($days->sum('total_protein') / $count);
        $this->averageFat = (int) round($days->sum('total_fat') / $count);
        $this->averageCarbs = (int) round($days->sum('total_carbs') / $count);
    }
}
