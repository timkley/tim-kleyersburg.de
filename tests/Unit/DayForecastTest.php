<?php

declare(strict_types=1);

use App\Data\DayForecast;
use Carbon\CarbonImmutable;

it('can be instantiated with all properties', function () {
    $date = CarbonImmutable::parse('2026-03-01');

    $forecast = new DayForecast(
        date: $date,
        minTemp: 3.5,
        maxTemp: 12.8,
        rain: 2.4,
        condition: 'Slight rain',
        wmoCode: 61,
    );

    expect($forecast->date)->toBe($date)
        ->and($forecast->minTemp)->toBe(3.5)
        ->and($forecast->maxTemp)->toBe(12.8)
        ->and($forecast->rain)->toBe(2.4)
        ->and($forecast->condition)->toBe('Slight rain')
        ->and($forecast->wmoCode)->toBe(61);
});

it('stores zero values correctly', function () {
    $forecast = new DayForecast(
        date: CarbonImmutable::parse('2026-03-01'),
        minTemp: 0.0,
        maxTemp: 0.0,
        rain: 0.0,
        condition: 'Clear sky',
        wmoCode: 0,
    );

    expect($forecast->minTemp)->toBe(0.0)
        ->and($forecast->maxTemp)->toBe(0.0)
        ->and($forecast->rain)->toBe(0.0)
        ->and($forecast->wmoCode)->toBe(0);
});

it('stores negative temperatures', function () {
    $forecast = new DayForecast(
        date: CarbonImmutable::parse('2026-01-15'),
        minTemp: -8.5,
        maxTemp: -1.2,
        rain: 0.0,
        condition: 'Clear sky',
        wmoCode: 0,
    );

    expect($forecast->minTemp)->toBe(-8.5)
        ->and($forecast->maxTemp)->toBe(-1.2);
});
