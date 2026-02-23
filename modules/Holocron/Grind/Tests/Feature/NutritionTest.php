<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
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

it('can edit a meal', function () {
    $day = NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
        'meals' => [
            ['name' => 'Frühstück', 'time' => '08:00', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50],
            ['name' => 'Mittagessen', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70],
        ],
        'total_kcal' => 1200,
        'total_protein' => 70,
        'total_fat' => 45,
        'total_carbs' => 120,
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('editMeal', 0)
        ->assertSet('editingMealIndex', 0)
        ->assertSet('mealName', 'Frühstück')
        ->set('mealName', 'Pre-Workout')
        ->set('mealTime', '07:30')
        ->set('mealKcal', 550)
        ->set('mealProtein', 35)
        ->set('mealFat', 18)
        ->set('mealCarbs', 60)
        ->call('updateMeal')
        ->assertSet('editingMealIndex', null)
        ->assertHasNoErrors();

    $day->refresh();

    expect($day->meals)->toHaveCount(2)
        ->and($day->meals[0]['name'])->toBe('Pre-Workout')
        ->and($day->meals[0]['time'])->toBe('07:30')
        ->and($day->meals[1]['name'])->toBe('Mittagessen')
        ->and($day->total_kcal)->toBe(1250)
        ->and($day->total_protein)->toBe(75)
        ->and($day->total_fat)->toBe(43)
        ->and($day->total_carbs)->toBe(130);
});

it('can update day type', function () {
    NutritionDay::factory()->rest()->create([
        'date' => now()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->set('dayType', 'training')
        ->assertHasNoErrors();

    $day = NutritionDay::query()->whereDate('date', now()->format('Y-m-d'))->first();

    expect($day->type)->toBe('training');
});

it('updates projected protein goal target when day type changes', function () {
    NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
        'type' => 'rest',
        'meals' => [
            ['name' => 'Meal', 'kcal' => 500, 'protein' => 100, 'fat' => 20, 'carbs' => 50],
        ],
        'total_kcal' => 500,
        'total_protein' => 100,
        'total_fat' => 20,
        'total_carbs' => 50,
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->set('dayType', 'training')
        ->assertHasNoErrors();

    $goal = DailyGoal::query()
        ->where('type', GoalType::Protein)
        ->whereDate('date', now()->format('Y-m-d'))
        ->first();

    expect($goal)->not->toBeNull()
        ->and($goal->amount)->toBe(100)
        ->and($goal->goal)->toBe(155);
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
