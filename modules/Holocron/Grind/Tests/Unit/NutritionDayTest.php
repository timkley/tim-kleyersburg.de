<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

it('casts meals to array', function () {
    $day = NutritionDay::factory()->create();

    expect($day->meals)->toBeArray();
});

it('casts date to carbon', function () {
    $day = NutritionDay::factory()->create();

    expect($day->date)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('recalculates totals from meals', function () {
    $day = NutritionDay::factory()->create([
        'meals' => [
            ['name' => 'Meal 1', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
            ['name' => 'Meal 2', 'kcal' => 300, 'protein' => 25, 'fat' => 10, 'carbs' => 30],
        ],
        'total_kcal' => 0,
        'total_protein' => 0,
        'total_fat' => 0,
        'total_carbs' => 0,
    ]);

    $day->recalculateTotals();

    expect($day->total_kcal)->toBe(800)
        ->and($day->total_protein)->toBe(55)
        ->and($day->total_fat)->toBe(30)
        ->and($day->total_carbs)->toBe(80);
});

it('projects nutrition protein totals into daily protein goal', function () {
    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $tim->settings()->create([
        'weight' => 80,
        'nutrition_daily_targets' => [
            'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
            'rest' => ['kcal' => 2000, 'protein' => 140, 'fat' => 60, 'carbs' => 185],
        ],
    ]);

    $day = NutritionDay::factory()->create([
        'date' => today()->toDateString(),
        'type' => 'training',
        'meals' => [
            ['name' => 'Meal 1', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
            ['name' => 'Meal 2', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70],
        ],
        'total_kcal' => 0,
        'total_protein' => 0,
        'total_fat' => 0,
        'total_carbs' => 0,
    ]);

    $day->recalculateTotals();

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $day->date)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(70)
        ->and($goal->goal)->toBe(155);
});

it('falls back to weight based target when day type target is missing', function () {
    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $tim->settings()->create([
        'weight' => 82,
        'nutrition_daily_targets' => [
            'rest' => ['kcal' => 2000, 'protein' => 140, 'fat' => 60, 'carbs' => 185],
        ],
    ]);

    $day = NutritionDay::factory()->create([
        'date' => today()->toDateString(),
        'type' => 'training',
        'meals' => [
            ['name' => 'Meal', 'kcal' => 400, 'protein' => 50, 'fat' => 12, 'carbs' => 30],
        ],
        'total_kcal' => 0,
        'total_protein' => 0,
        'total_fat' => 0,
        'total_carbs' => 0,
    ]);

    $day->recalculateTotals();

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $day->date)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->goal)->toBe(164)
        ->and($goal->amount)->toBe(50);
});

it('marks a day as a given type via markAsDayType', function () {
    NutritionDay::factory()->rest()->create([
        'date' => today()->toDateString(),
    ]);

    NutritionDay::markAsDayType('training', 'Upper');

    $day = NutritionDay::query()->whereDate('date', today())->first();

    expect($day->type)->toBe('training')
        ->and($day->training_label)->toBe('Upper');
});

it('creates a nutrition day if none exists when marking day type', function () {
    expect(NutritionDay::query()->count())->toBe(0);

    NutritionDay::markAsDayType('training', 'Lower');

    $day = NutritionDay::query()->whereDate('date', today())->first();

    expect($day)->not->toBeNull()
        ->and($day->type)->toBe('training')
        ->and($day->training_label)->toBe('Lower');
});

it('preserves training label when not explicitly provided', function () {
    NutritionDay::factory()->training('Upper')->create([
        'date' => today()->toDateString(),
    ]);

    NutritionDay::markAsDayType('rest');

    $day = NutritionDay::query()->whereDate('date', today())->first();

    expect($day->type)->toBe('rest')
        ->and($day->training_label)->toBe('Upper');
});

it('marks a day type for a specific date', function () {
    $yesterday = today()->subDay();

    NutritionDay::factory()->rest()->create([
        'date' => $yesterday->toDateString(),
    ]);

    NutritionDay::markAsDayType('training', 'Lower', $yesterday);

    $day = NutritionDay::query()->whereDate('date', $yesterday)->first();

    expect($day->type)->toBe('training')
        ->and($day->training_label)->toBe('Lower');
});
