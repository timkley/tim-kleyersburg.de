<?php

declare(strict_types=1);

use App\Ai\Tools\LogMeal;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

// Use a far-future date to avoid collisions with data imported by the migration seeder.
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

it('returns the expected schema definition', function () {
    $tool = new LogMeal;

    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)
        ->toHaveKeys(['date', 'name', 'kcal', 'protein', 'fat', 'carbs', 'time', 'day_type'])
        ->and($schema['date'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['name'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['kcal'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class)
        ->and($schema['protein'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class)
        ->and($schema['fat'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class)
        ->and($schema['carbs'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class)
        ->and($schema['time'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['day_type'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class);
});

it('syncs protein daily goal projection when logging a meal', function () {
    $tool = new LogMeal;

    $tool->handle(new Request([
        'date' => $this->testDate,
        'name' => 'Protein Shake',
        'kcal' => 110,
        'protein' => 23,
        'fat' => 0,
        'carbs' => 3,
        'day_type' => 'training',
    ]));

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', $this->testDate)
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(23)
        ->and($goal->goal)->toBe(155);
});
