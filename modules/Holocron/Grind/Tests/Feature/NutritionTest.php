<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\Meal;
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

    $day = NutritionDay::query()->with('meals')->whereDate('date', now()->format('Y-m-d'))->first();

    expect($day)->not->toBeNull()
        ->and($day->meals)->toHaveCount(1)
        ->and($day->meals->first()->name)->toBe('Frühstück')
        ->and($day->meals->first()->time)->toBe('08:00')
        ->and($day->meals->first()->kcal)->toBe(500)
        ->and($day->meals->first()->protein)->toBe(30)
        ->and($day->meals->first()->fat)->toBe(20)
        ->and($day->meals->first()->carbs)->toBe(50);
});

it('can delete a meal', function () {
    $day = NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
    ]);
    $meal1 = Meal::factory()->create(['nutrition_day_id' => $day->id, 'name' => 'Frühstück', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50]);
    Meal::factory()->create(['nutrition_day_id' => $day->id, 'name' => 'Mittagessen', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('deleteMeal', $meal1->id)
        ->assertHasNoErrors();

    $day->refresh();

    expect($day->meals)->toHaveCount(1)
        ->and($day->meals->first()->name)->toBe('Mittagessen');
});

it('can edit a meal', function () {
    $day = NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
    ]);
    $meal1 = Meal::factory()->create(['nutrition_day_id' => $day->id, 'name' => 'Frühstück', 'time' => '08:00', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50]);
    Meal::factory()->create(['nutrition_day_id' => $day->id, 'name' => 'Mittagessen', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('editMeal', $meal1->id)
        ->assertSet('editingMealId', $meal1->id)
        ->assertSet('mealName', 'Frühstück')
        ->set('mealName', 'Pre-Workout')
        ->set('mealTime', '07:30')
        ->set('mealKcal', 550)
        ->set('mealProtein', 35)
        ->set('mealFat', 18)
        ->set('mealCarbs', 60)
        ->call('updateMeal')
        ->assertSet('editingMealId', null)
        ->assertHasNoErrors();

    $day->load('meals');

    $updatedMeal = $day->meals->firstWhere('id', $meal1->id);

    expect($day->meals)->toHaveCount(2)
        ->and($updatedMeal->name)->toBe('Pre-Workout')
        ->and($updatedMeal->time)->toBe('07:30')
        ->and($updatedMeal->kcal)->toBe(550)
        ->and($updatedMeal->protein)->toBe(35)
        ->and($updatedMeal->fat)->toBe(18)
        ->and($updatedMeal->carbs)->toBe(60);
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
    $day = NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
        'type' => 'rest',
    ]);
    Meal::factory()->create(['nutrition_day_id' => $day->id, 'name' => 'Meal', 'kcal' => 500, 'protein' => 100, 'fat' => 20, 'carbs' => 50]);

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
        $day = NutritionDay::factory()->create([
            'date' => now()->subDays($daysAgo)->format('Y-m-d'),
        ]);
        Meal::factory()->create([
            'nutrition_day_id' => $day->id,
            'name' => 'Meal',
            'kcal' => 2100,
            'protein' => 150,
            'fat' => 60,
            'carbs' => 200,
        ]);
    }

    Livewire::test('holocron.grind.nutrition.index')
        ->assertSet('averageKcal', 2100)
        ->assertSet('averageProtein', 150)
        ->assertSet('averageFat', 60)
        ->assertSet('averageCarbs', 200);
});

it('can navigate to the next day', function () {
    $tomorrow = now()->addDay()->format('Y-m-d');

    Livewire::test('holocron.grind.nutrition.index')
        ->call('nextDay')
        ->assertSet('date', $tomorrow);
});

it('can navigate to a specific date', function () {
    $targetDate = '2025-06-15';

    Livewire::test('holocron.grind.nutrition.index')
        ->call('goToDate', $targetDate)
        ->assertSet('date', $targetDate);
});

it('can cancel a meal edit', function () {
    $day = NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
    ]);
    $meal = Meal::factory()->create(['nutrition_day_id' => $day->id, 'name' => 'Frühstück', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('editMeal', $meal->id)
        ->assertSet('editingMealId', $meal->id)
        ->assertSet('mealName', 'Frühstück')
        ->call('cancelMealEdit')
        ->assertSet('editingMealId', null)
        ->assertSet('mealName', '')
        ->assertSet('mealKcal', null)
        ->assertSet('mealProtein', null);
});

it('can set training label', function () {
    NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('setTrainingLabel', 'Upper Body');

    $day = NutritionDay::query()->whereDate('date', now()->format('Y-m-d'))->first();

    expect($day->training_label)->toBe('Upper Body');
});

it('can update notes', function () {
    NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('updateNotes', 'Felt great today');

    $day = NutritionDay::query()->whereDate('date', now()->format('Y-m-d'))->first();

    expect($day->notes)->toBe('Felt great today');
});

it('validates meal name is required when adding', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->set('mealName', '')
        ->set('mealKcal', 500)
        ->set('mealProtein', 30)
        ->set('mealFat', 20)
        ->set('mealCarbs', 50)
        ->call('addMeal')
        ->assertHasErrors(['mealName']);
});

it('validates meal kcal is required when adding', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->set('mealName', 'Frühstück')
        ->set('mealKcal', null)
        ->set('mealProtein', 30)
        ->set('mealFat', 20)
        ->set('mealCarbs', 50)
        ->call('addMeal')
        ->assertHasErrors(['mealKcal']);
});

it('ignores deleting a meal with invalid id', function () {
    $day = NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
    ]);
    Meal::factory()->create(['nutrition_day_id' => $day->id, 'name' => 'Frühstück', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('deleteMeal', 99999);

    $day->refresh();
    expect($day->meals)->toHaveCount(1);
});

it('ignores editing a meal when no day exists', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->call('editMeal', 1)
        ->assertSet('editingMealId', null);
});

it('ignores editing a meal with invalid id', function () {
    NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('editMeal', 99999)
        ->assertSet('editingMealId', null);
});

it('does not update meal when editingMealId is null', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->call('updateMeal');

    expect(NutritionDay::query()->whereDate('date', now()->format('Y-m-d'))->count())->toBe(0);
});

it('loads day type when navigating days', function () {
    NutritionDay::factory()->training()->create([
        'date' => now()->subDay()->format('Y-m-d'),
    ]);

    Livewire::test('holocron.grind.nutrition.index')
        ->assertSet('dayType', 'rest')
        ->call('previousDay')
        ->assertSet('dayType', 'training');
});

it('defaults to rest when no day exists', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->assertSet('dayType', 'rest');
});

it('creates day with rest type when adding meal to empty date', function () {
    Livewire::test('holocron.grind.nutrition.index')
        ->set('mealName', 'Snack')
        ->set('mealKcal', 200)
        ->set('mealProtein', 10)
        ->set('mealFat', 5)
        ->set('mealCarbs', 30)
        ->call('addMeal')
        ->assertHasNoErrors();

    $day = NutritionDay::query()->whereDate('date', now()->format('Y-m-d'))->first();

    expect($day)->not->toBeNull()
        ->and($day->type)->toBe('rest');
});

it('cancels meal edit when updating with a deleted meal', function () {
    $day = NutritionDay::factory()->create([
        'date' => now()->format('Y-m-d'),
    ]);
    $meal = Meal::factory()->create(['nutrition_day_id' => $day->id, 'name' => 'Frühstück', 'kcal' => 500, 'protein' => 30, 'fat' => 20, 'carbs' => 50]);

    Livewire::test('holocron.grind.nutrition.index')
        ->call('editMeal', $meal->id)
        ->assertSet('editingMealId', $meal->id)
        ->call('deleteMeal', $meal->id)
        ->assertSet('editingMealId', null)
        ->assertSet('mealName', '');

    $day->refresh();
    expect($day->meals)->toHaveCount(0);
});
