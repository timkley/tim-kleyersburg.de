<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Meal;
use Modules\Holocron\Grind\Models\NutritionDay;

it('casts date to carbon', function () {
    $day = NutritionDay::factory()->create();

    expect($day->date)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('has many meals', function () {
    $day = NutritionDay::factory()->create();
    Meal::factory()->count(2)->create(['nutrition_day_id' => $day->id]);

    expect($day->meals)->toHaveCount(2)
        ->each->toBeInstanceOf(Meal::class);
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
