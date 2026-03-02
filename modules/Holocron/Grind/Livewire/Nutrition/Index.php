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

    public ?int $editingMealId = null;

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
        $this->cancelMealEdit();
        $this->calculateAverages();
    }

    public function nextDay(): void
    {
        $this->date = Carbon::parse($this->date)->addDay()->format('Y-m-d');
        $this->loadDayType();
        $this->cancelMealEdit();
        $this->calculateAverages();
    }

    public function goToDate(string $date): void
    {
        $this->date = $date;
        $this->loadDayType();
        $this->cancelMealEdit();
        $this->calculateAverages();
    }

    public function updatedDayType(string $type): void
    {
        NutritionDay::markAsDayType($type, date: Carbon::parse($this->date));
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
        $this->validateMealInput();

        $day = $this->getOrCreateDay();

        $day->meals()->create($this->mealPayload());

        $this->cancelMealEdit();
        $this->calculateAverages();
    }

    public function deleteMeal(int $mealId): void
    {
        $day = NutritionDay::query()->whereDate('date', $this->date)->first();

        if (! $day) {
            return;
        }

        $meal = $day->meals()->find($mealId);

        if (! $meal) {
            return;
        }

        $meal->delete();

        if ($this->editingMealId === $mealId) {
            $this->cancelMealEdit();
        }

        $this->calculateAverages();
    }

    public function editMeal(int $mealId): void
    {
        $day = NutritionDay::query()->whereDate('date', $this->date)->first();

        if (! $day) {
            return;
        }

        $meal = $day->meals()->find($mealId);

        if (! $meal) {
            return;
        }

        $this->editingMealId = $mealId;
        $this->mealName = $meal->name;
        $this->mealTime = $meal->time;
        $this->mealKcal = $meal->kcal;
        $this->mealProtein = $meal->protein;
        $this->mealFat = $meal->fat;
        $this->mealCarbs = $meal->carbs;
    }

    public function updateMeal(): void
    {
        if ($this->editingMealId === null) {
            return;
        }

        $this->validateMealInput();

        $day = NutritionDay::query()->whereDate('date', $this->date)->first();

        if (! $day) {
            $this->cancelMealEdit();

            return;
        }

        $meal = $day->meals()->find($this->editingMealId);

        if (! $meal) {
            $this->cancelMealEdit();

            return;
        }

        $meal->update($this->mealPayload());

        $this->cancelMealEdit();
        $this->calculateAverages();
    }

    public function cancelMealEdit(): void
    {
        $this->reset('mealName', 'mealTime', 'mealKcal', 'mealProtein', 'mealFat', 'mealCarbs');
        $this->editingMealId = null;
    }

    public function render(): View
    {
        $day = NutritionDay::query()->with('meals')->whereDate('date', $this->date)->first();

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
        ]);
    }

    private function calculateAverages(): void
    {
        $days = NutritionDay::query()
            ->with('meals')
            ->whereDate('date', '<=', $this->date)
            ->whereDate('date', '>=', Carbon::parse($this->date)->subDays(6)->format('Y-m-d'))
            ->get();

        $count = max(1, $days->count());

        $this->averageKcal = (int) round($days->sum(fn (NutritionDay $day) => $day->meals->sum('kcal')) / $count);
        $this->averageProtein = (int) round($days->sum(fn (NutritionDay $day) => $day->meals->sum('protein')) / $count);
        $this->averageFat = (int) round($days->sum(fn (NutritionDay $day) => $day->meals->sum('fat')) / $count);
        $this->averageCarbs = (int) round($days->sum(fn (NutritionDay $day) => $day->meals->sum('carbs')) / $count);
    }

    private function validateMealInput(): void
    {
        $this->validate([
            'mealName' => 'required|string',
            'mealKcal' => 'required|integer|min:0',
            'mealProtein' => 'required|integer|min:0',
            'mealFat' => 'required|integer|min:0',
            'mealCarbs' => 'required|integer|min:0',
        ]);
    }

    /**
     * @return array{name: string, time: ?string, kcal: int, protein: int, fat: int, carbs: int}
     */
    private function mealPayload(): array
    {
        return [
            'name' => $this->mealName,
            'time' => $this->mealTime,
            'kcal' => (int) $this->mealKcal,
            'protein' => (int) $this->mealProtein,
            'fat' => (int) $this->mealFat,
            'carbs' => (int) $this->mealCarbs,
        ];
    }
}
