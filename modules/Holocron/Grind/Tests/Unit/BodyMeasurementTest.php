<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Modules\Holocron\Grind\Models\BodyMeasurement;

test('factory creates a valid body measurement', function () {
    $measurement = BodyMeasurement::factory()->create();

    expect($measurement)->toBeInstanceOf(BodyMeasurement::class)
        ->and($measurement->exists)->toBeTrue();
});

test('casts date to carbon', function () {
    $measurement = BodyMeasurement::factory()->create(['date' => '2025-06-15']);

    expect($measurement->date)->toBeInstanceOf(CarbonImmutable::class)
        ->and($measurement->date->format('Y-m-d'))->toBe('2025-06-15');
});

test('casts weight to decimal with two places', function () {
    $measurement = BodyMeasurement::factory()->create(['weight' => 80.55]);

    expect((float) $measurement->weight)->toBe(80.55);
});

test('casts body_fat to decimal with one place', function () {
    $measurement = BodyMeasurement::factory()->create(['body_fat' => 18.3]);

    expect((float) $measurement->body_fat)->toBe(18.3);
});

test('casts muscle_mass to decimal with one place', function () {
    $measurement = BodyMeasurement::factory()->create(['muscle_mass' => 55.7]);

    expect((float) $measurement->muscle_mass)->toBe(55.7);
});

test('casts bmi to decimal with one place', function () {
    $measurement = BodyMeasurement::factory()->create(['bmi' => 24.1]);

    expect((float) $measurement->bmi)->toBe(24.1);
});

test('casts body_water to decimal with one place', function () {
    $measurement = BodyMeasurement::factory()->create(['body_water' => 58.5]);

    expect((float) $measurement->body_water)->toBe(58.5);
});

test('nullable fields can be null', function () {
    $measurement = BodyMeasurement::factory()->create([
        'body_fat' => null,
        'muscle_mass' => null,
        'visceral_fat' => null,
        'bmi' => null,
        'body_water' => null,
    ]);

    expect($measurement->body_fat)->toBeNull()
        ->and($measurement->muscle_mass)->toBeNull()
        ->and($measurement->visceral_fat)->toBeNull()
        ->and($measurement->bmi)->toBeNull()
        ->and($measurement->body_water)->toBeNull();
});

test('uses grind_body_measurements table', function () {
    expect((new BodyMeasurement)->getTable())->toBe('grind_body_measurements');
});
