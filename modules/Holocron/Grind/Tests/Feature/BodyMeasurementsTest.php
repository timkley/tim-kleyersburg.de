<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Grind\Models\BodyMeasurement;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $user = User::factory()
        ->has(UserSetting::factory(), 'settings')
        ->create();

    actingAs($user);

    BodyMeasurement::query()->delete();
});

it('is not reachable when unauthenticated', function () {
    auth()->logout();

    get(route('holocron.grind.body-measurements'))
        ->assertRedirect();
});

it('renders the body measurements page', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->assertSuccessful();
});

it('lists measurements newest first', function () {
    BodyMeasurement::factory()->create([
        'date' => '2025-01-01',
        'weight' => 80.00,
    ]);

    BodyMeasurement::factory()->create([
        'date' => '2025-01-10',
        'weight' => 78.00,
    ]);

    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->assertSeeInOrder(['78', '80']);
});

it('can add a measurement', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->set('date', '2025-03-15')
        ->set('weight', 79.5)
        ->set('bodyFat', 18.2)
        ->set('muscleMass', 55.3)
        ->set('visceralFat', 6)
        ->set('bmi', 24.1)
        ->set('bodyWater', 58.5)
        ->call('addMeasurement')
        ->assertHasNoErrors();

    expect(BodyMeasurement::query()->count())->toBe(1);

    $measurement = BodyMeasurement::query()->first();

    expect($measurement->date->format('Y-m-d'))->toBe('2025-03-15')
        ->and((float) $measurement->weight)->toBe(79.50)
        ->and((float) $measurement->body_fat)->toBe(18.2)
        ->and((float) $measurement->muscle_mass)->toBe(55.3)
        ->and($measurement->visceral_fat)->toBe(6)
        ->and((float) $measurement->bmi)->toBe(24.1)
        ->and((float) $measurement->body_water)->toBe(58.5);
});

it('provides chart data for weight and muscle mass', function () {
    BodyMeasurement::factory()->create([
        'date' => '2025-01-01',
        'weight' => 80.00,
        'muscle_mass' => 55.0,
    ]);

    BodyMeasurement::factory()->create([
        'date' => '2025-01-10',
        'weight' => 78.50,
        'muscle_mass' => 56.0,
    ]);

    $component = Livewire::test('holocron.grind.nutrition.body-measurements');

    $chartData = $component->viewData('chartData');

    expect($chartData)->toHaveCount(2)
        ->and($chartData[0]['date'])->toBe('01.01.2025')
        ->and($chartData[0]['weight'])->toBe(80.0)
        ->and($chartData[0]['muscle_mass'])->toBe(55.0)
        ->and($chartData[1]['date'])->toBe('10.01.2025')
        ->and($chartData[1]['weight'])->toBe(78.5)
        ->and($chartData[1]['muscle_mass'])->toBe(56.0);
});

it('validates weight is required when adding measurement', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->set('date', '2025-03-15')
        ->set('weight', null)
        ->call('addMeasurement')
        ->assertHasErrors(['weight' => 'required']);
});

it('validates date is required when adding measurement', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->set('date', '')
        ->set('weight', 79.5)
        ->call('addMeasurement')
        ->assertHasErrors(['date' => 'required']);
});

it('defaults date to today', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->assertSet('date', now()->format('Y-m-d'));
});

it('can add measurement with only weight', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->set('date', '2025-03-15')
        ->set('weight', 80.0)
        ->call('addMeasurement')
        ->assertHasNoErrors();

    $measurement = BodyMeasurement::query()->first();

    expect($measurement)->not->toBeNull()
        ->and((float) $measurement->weight)->toBe(80.0)
        ->and($measurement->body_fat)->toBeNull()
        ->and($measurement->muscle_mass)->toBeNull();
});

it('can add multiple measurements for different dates', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->set('date', '2025-03-15')
        ->set('weight', 80.0)
        ->call('addMeasurement')
        ->assertHasNoErrors();

    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->set('date', '2025-03-16')
        ->set('weight', 79.0)
        ->call('addMeasurement')
        ->assertHasNoErrors();

    expect(BodyMeasurement::query()->count())->toBe(2);
});

it('resets form fields after adding measurement', function () {
    Livewire::test('holocron.grind.nutrition.body-measurements')
        ->set('date', '2025-03-15')
        ->set('weight', 80.0)
        ->set('bodyFat', 18.0)
        ->set('muscleMass', 55.0)
        ->set('visceralFat', 6)
        ->set('bmi', 24.0)
        ->set('bodyWater', 58.0)
        ->call('addMeasurement')
        ->assertSet('weight', null)
        ->assertSet('bodyFat', null)
        ->assertSet('muscleMass', null)
        ->assertSet('visceralFat', null)
        ->assertSet('bmi', null)
        ->assertSet('bodyWater', null);
});

it('handles chart data with null muscle mass', function () {
    BodyMeasurement::factory()->create([
        'date' => '2025-01-01',
        'weight' => 80.00,
        'muscle_mass' => null,
    ]);

    $component = Livewire::test('holocron.grind.nutrition.body-measurements');

    $chartData = $component->viewData('chartData');

    expect($chartData)->toHaveCount(1)
        ->and($chartData[0]['muscle_mass'])->toBeNull();
});
