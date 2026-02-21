<?php

declare(strict_types=1);

use App\Ai\Tools\LogMeal;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;

// Use a far-future date to avoid collisions with data imported by the migration seeder.
beforeEach(function () {
    $this->testDate = today()->addYear()->toDateString();
});

it('logs a meal to a new day', function () {
    $tool = new LogMeal;

    $request = new Request([
        'date' => $this->testDate,
        'name' => 'Protein Shake',
        'kcal' => 110,
        'protein' => 23,
        'fat' => 0,
        'carbs' => 3,
    ]);

    $result = $tool->handle($request);

    expect($result)->toContain('Protein Shake');

    $day = NutritionDay::query()->whereDate('date', $this->testDate)->first();
    expect($day)->not->toBeNull()
        ->and($day->meals)->toHaveCount(1)
        ->and($day->total_kcal)->toBe(110);
});

it('appends a meal to an existing day', function () {
    NutritionDay::factory()->create([
        'date' => $this->testDate,
        'meals' => [['name' => 'Existing', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50]],
        'total_kcal' => 500,
        'total_protein' => 30,
        'total_fat' => 20,
        'total_carbs' => 50,
    ]);

    $tool = new LogMeal;

    $request = new Request([
        'date' => $this->testDate,
        'name' => 'Shake',
        'kcal' => 110,
        'protein' => 23,
        'fat' => 0,
        'carbs' => 3,
    ]);

    $tool->handle($request);

    $day = NutritionDay::query()->whereDate('date', $this->testDate)->first();
    expect($day->meals)->toHaveCount(2)
        ->and($day->total_kcal)->toBe(610);
});

it('sets day type when creating a new day', function () {
    $tool = new LogMeal;

    $request = new Request([
        'date' => $this->testDate,
        'name' => 'Meal',
        'kcal' => 500,
        'protein' => 30,
        'fat' => 20,
        'carbs' => 50,
        'day_type' => 'training',
    ]);

    $tool->handle($request);

    expect(NutritionDay::query()->whereDate('date', $this->testDate)->first()->type)->toBe('training');
});

it('includes optional time in the meal data', function () {
    $tool = new LogMeal;

    $request = new Request([
        'date' => $this->testDate,
        'name' => 'Lunch',
        'kcal' => 700,
        'protein' => 40,
        'fat' => 25,
        'carbs' => 70,
        'time' => '12:30',
    ]);

    $tool->handle($request);

    $day = NutritionDay::query()->whereDate('date', $this->testDate)->first();
    expect($day->meals[0]['time'])->toBe('12:30');
});

it('defaults to rest day type when not specified', function () {
    $tool = new LogMeal;

    $request = new Request([
        'date' => $this->testDate,
        'name' => 'Snack',
        'kcal' => 200,
        'protein' => 10,
        'fat' => 5,
        'carbs' => 25,
    ]);

    $tool->handle($request);

    expect(NutritionDay::query()->whereDate('date', $this->testDate)->first()->type)->toBe('rest');
});

it('returns a confirmation string with daily totals', function () {
    $tool = new LogMeal;

    $request = new Request([
        'date' => $this->testDate,
        'name' => 'Oatmeal',
        'kcal' => 350,
        'protein' => 12,
        'fat' => 8,
        'carbs' => 55,
    ]);

    $result = $tool->handle($request);

    expect($result)
        ->toContain('Oatmeal')
        ->toContain('350 kcal')
        ->toContain('12g protein')
        ->toContain('8g fat')
        ->toContain('55g carbs')
        ->toContain('1 meals total');
});
