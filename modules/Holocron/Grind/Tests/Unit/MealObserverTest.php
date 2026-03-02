<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

beforeEach(function () {
    $this->testDate = today()->addYear()->toDateString();

    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $tim->settings()->create([
        'weight' => 80,
        'nutrition_daily_targets' => [
            'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
            'rest' => ['kcal' => 2000, 'protein' => 140, 'fat' => 60, 'carbs' => 185],
        ],
    ]);
});

it('syncs protein goal when a meal is created', function () {
    $day = NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'training',
    ]);

    Meal::factory()->create([
        'nutrition_day_id' => $day->id,
        'protein' => 30,
    ]);

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(30)
        ->and($goal->goal)->toBe(155);
});

it('updates protein goal when a meal is updated', function () {
    $day = NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'rest',
    ]);

    $meal = Meal::factory()->create([
        'nutrition_day_id' => $day->id,
        'protein' => 20,
    ]);

    $meal->update(['protein' => 40]);

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal->amount)->toBe(40);
});

it('updates protein goal when a meal is deleted', function () {
    $day = NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'rest',
    ]);

    $meal1 = Meal::factory()->create([
        'nutrition_day_id' => $day->id,
        'protein' => 20,
    ]);

    Meal::factory()->create([
        'nutrition_day_id' => $day->id,
        'protein' => 30,
    ]);

    $meal1->delete();

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal->amount)->toBe(30);
});
