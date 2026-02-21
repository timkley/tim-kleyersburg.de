<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\NutritionDay;

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
