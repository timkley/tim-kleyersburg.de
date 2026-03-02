<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;

it('belongs to a nutrition day', function () {
    $day = NutritionDay::factory()->create();
    $meal = Meal::factory()->create(['nutrition_day_id' => $day->id]);

    expect($meal->nutritionDay)->toBeInstanceOf(NutritionDay::class)
        ->and($meal->nutritionDay->id)->toBe($day->id);
});

it('can be created with factory defaults', function () {
    $meal = Meal::factory()->create();

    expect($meal)
        ->name->toBeString()
        ->kcal->toBeInt()
        ->protein->toBeInt()
        ->fat->toBeInt()
        ->carbs->toBeInt();
});

it('has a nutrition day with many meals', function () {
    $day = NutritionDay::factory()->create();
    Meal::factory()->count(3)->create(['nutrition_day_id' => $day->id]);

    expect($day->meals)->toHaveCount(3);
});
