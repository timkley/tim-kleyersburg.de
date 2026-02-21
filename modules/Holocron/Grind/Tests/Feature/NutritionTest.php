<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    NutritionDay::query()->delete();

    $user = User::factory()
        ->has(UserSetting::factory()->state([
            'nutrition_daily_targets' => [
                'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
                'rest' => ['kcal' => 2000, 'protein' => 155, 'fat' => 60, 'carbs' => 185],
                'sick' => ['kcal' => 2500, 'protein' => 155, 'fat' => 75, 'carbs' => 250],
            ],
        ]), 'settings')
        ->create();

    actingAs($user);
});

it('is not reachable when unauthenticated', function () {
    auth()->logout();

    get(route('holocron.grind.nutrition.index'))
        ->assertRedirect();
});

it('renders the nutrition page', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->assertSuccessful();
});

it('shows the current date by default', function () {
    NutritionDay::factory()->create(['date' => now()->format('Y-m-d')]);

    Livewire::test('holocron.grind.nutrition.index')
        ->assertSet('date', now()->format('Y-m-d'));
});

it('can navigate to a different date', function () {
    $yesterday = now()->subDay()->format('Y-m-d');

    Livewire::test('holocron.grind.nutrition.index')
        ->call('previousDay')
        ->assertSet('date', $yesterday);
});

it('can add a meal', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->set('mealName', 'Frühstück')
        ->set('mealTime', '08:00')
        ->set('mealKcal', 500)
        ->set('mealProtein', 30)
        ->set('mealFat', 20)
        ->set('mealCarbs', 50)
        ->call('addMeal')
        ->assertHasNoErrors();

    $day = NutritionDay::query()->whereDate('date', now()->format('Y-m-d'))->first();

    expect($day)->not->toBeNull()
        ->and($day->meals)->toHaveCount(1)
        ->and($day->meals[0]['name'])->toBe('Frühstück')
        ->and($day->meals[0]['time'])->toBe('08:00')
        ->and($day->meals[0]['kcal'])->toBe(500)
        ->and($day->meals[0]['protein'])->toBe(30)
        ->and($day->meals[0]['fat'])->toBe(20)
        ->and($day->meals[0]['carbs'])->toBe(50)
        ->and($day->total_kcal)->toBe(500)
        ->and($day->total_protein)->toBe(30);
});

it('can delete a meal', function () {
    $day = NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
        'meals' => [
            ['name' => 'Frühstück', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
            ['name' => 'Mittagessen', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70],
        ],
        'total_kcal' => 1200,
        'total_protein' => 70,
        'total_fat' => 45,
        'total_carbs' => 120,
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('deleteMeal', 0)
        ->assertHasNoErrors();

    $day->refresh();

    expect($day->meals)->toHaveCount(1)
        ->and($day->meals[0]['name'])->toBe('Mittagessen')
        ->and($day->total_kcal)->toBe(700)
        ->and($day->total_protein)->toBe(40);
});

it('can update day type', function () {
    NutritionDay::factory()->rest()->create([
        'date' => now()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('setDayType', 'training')
        ->assertHasNoErrors();

    $day = NutritionDay::query()->whereDate('date', now()->format('Y-m-d'))->first();

    expect($day->type)->toBe('training');
});

it('calculates 7-day rolling average', function () {
    foreach (range(0, 6) as $daysAgo) {
        NutritionDay::factory()->create([
            'date' => now()->subDays($daysAgo)->format('Y-m-d'),
            'total_kcal' => 2100,
            'total_protein' => 150,
            'total_fat' => 60,
            'total_carbs' => 200,
        ]);
    }

    Livewire::test('holocron.grind.nutrition.index')
        ->assertSet('averageKcal', 2100)
        ->assertSet('averageProtein', 150)
        ->assertSet('averageFat', 60)
        ->assertSet('averageCarbs', 200);
});
