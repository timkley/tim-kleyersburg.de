<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Observers;

use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

class MealObserver
{
    public function created(Meal $meal): void
    {
        $this->syncProteinGoal($meal->nutritionDay);
    }

    public function updated(Meal $meal): void
    {
        $this->syncProteinGoal($meal->nutritionDay);
    }

    public function deleted(Meal $meal): void
    {
        $this->syncProteinGoal($meal->nutritionDay);
    }

    private function syncProteinGoal(NutritionDay $day): void
    {
        $user = $this->resolveGoalUser();

        if ($user === null) {
            return;
        }

        $totalProtein = (int) $day->meals()->sum('protein');

        $goal = DailyGoal::query()->firstOrNew(
            [
                'date' => $day->date->toDateString(),
                'type' => GoalType::Protein->value,
            ],
            [
                'unit' => GoalType::Protein->unit()->value,
            ],
        );

        $goal->fill([
            'unit' => GoalType::Protein->unit()->value,
            'goal' => $this->proteinTargetFor($user, $day),
            'amount' => $totalProtein,
        ]);

        $goal->save();
    }

    private function proteinTargetFor(User $user, NutritionDay $day): int
    {
        $target = $user->settings?->nutrition_daily_targets[$day->type]['protein'] ?? null;

        if (is_numeric($target)) {
            return (int) $target;
        }

        $weight = $user->settings?->weight;

        if ($weight === null) {
            return 0;
        }

        return (int) round($weight * 2);
    }

    private function resolveGoalUser(): ?User
    {
        $authenticatedUser = auth()->user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        return User::query()
            ->where('email', 'timkley@gmail.com')
            ->first();
    }
}
