<?php

declare(strict_types=1);

use App\Ai\Tools\EditMeal;
use Laravel\Ai\Tools\Request;
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

it('edits an existing meal and recalculates daily totals', function () {
    NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'training',
        'meals' => [
            ['name' => 'Frühstück', 'time' => '08:00', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
            ['name' => 'Mittagessen', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70],
        ],
        'total_kcal' => 1200,
        'total_protein' => 70,
        'total_fat' => 45,
        'total_carbs' => 120,
    ]);

    $tool = new EditMeal;

    $result = $tool->handle(new Request([
        'date' => $this->testDate,
        'meal_index' => 0,
        'name' => 'Pre-Workout',
        'time' => '07:30',
        'kcal' => 550,
        'protein' => 35,
        'fat' => 18,
        'carbs' => 60,
    ]));

    expect($result)->toContain('Meal at index 0 updated');

    $day = NutritionDay::query()->whereDate('date', $this->testDate)->first();
    expect($day)->not->toBeNull()
        ->and($day->meals[0]['name'])->toBe('Pre-Workout')
        ->and($day->meals[0]['time'])->toBe('07:30')
        ->and($day->meals[0]['kcal'])->toBe(550)
        ->and($day->meals[0]['protein'])->toBe(35)
        ->and($day->meals[0]['fat'])->toBe(18)
        ->and($day->meals[0]['carbs'])->toBe(60)
        ->and($day->meals[1]['name'])->toBe('Mittagessen')
        ->and($day->total_kcal)->toBe(1250)
        ->and($day->total_protein)->toBe(75)
        ->and($day->total_fat)->toBe(43)
        ->and($day->total_carbs)->toBe(130);
});

it('can clear the meal time', function () {
    NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'rest',
        'meals' => [
            ['name' => 'Lunch', 'time' => '12:30', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70],
        ],
        'total_kcal' => 700,
        'total_protein' => 40,
        'total_fat' => 25,
        'total_carbs' => 70,
    ]);

    $tool = new EditMeal;

    $tool->handle(new Request([
        'date' => $this->testDate,
        'meal_index' => 0,
        'time' => '',
    ]));

    $day = NutritionDay::query()->whereDate('date', $this->testDate)->first();
    expect($day->meals[0])->not->toHaveKey('time');
});

it('returns a not found message when no day exists', function () {
    $tool = new EditMeal;

    $result = $tool->handle(new Request([
        'date' => $this->testDate,
        'meal_index' => 0,
        'kcal' => 500,
    ]));

    expect($result)->toContain("No nutrition data for {$this->testDate}");
});

it('returns a not found message when the meal index is invalid', function () {
    NutritionDay::factory()->create([
        'date' => $this->testDate,
        'meals' => [
            ['name' => 'Meal', 'kcal' => 400, 'protein' => 25, 'fat' => 12, 'carbs' => 40],
        ],
        'total_kcal' => 400,
        'total_protein' => 25,
        'total_fat' => 12,
        'total_carbs' => 40,
    ]);

    $tool = new EditMeal;

    $result = $tool->handle(new Request([
        'date' => $this->testDate,
        'meal_index' => 5,
        'kcal' => 450,
    ]));

    expect($result)->toContain('Meal index 5 not found');
});

it('syncs protein daily goal projection when editing a meal', function () {
    NutritionDay::factory()->create([
        'date' => $this->testDate,
        'type' => 'training',
        'meals' => [
            ['name' => 'Meal', 'kcal' => 400, 'protein' => 20, 'fat' => 12, 'carbs' => 40],
        ],
        'total_kcal' => 400,
        'total_protein' => 20,
        'total_fat' => 12,
        'total_carbs' => 40,
    ]);

    $tool = new EditMeal;

    $tool->handle(new Request([
        'date' => $this->testDate,
        'meal_index' => 0,
        'protein' => 50,
    ]));

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(50)
        ->and($goal->goal)->toBe(155);
});
