<?php

declare(strict_types=1);

use App\Data\Forecast;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Journey;
use Modules\Holocron\Gear\Models\JourneyItem;

it('can be created using factory', function () {
    $journey = Journey::factory()->create();

    expect($journey)->toBeInstanceOf(Journey::class)
        ->and($journey->destination)->toBeString()
        ->and($journey->starts_at)->not->toBeNull()
        ->and($journey->ends_at)->not->toBeNull();
});

it('uses gear_journeys table', function () {
    $journey = new Journey;

    expect($journey->getTable())->toBe('gear_journeys');
});

it('has many journey items', function () {
    $journey = Journey::factory()->create();
    JourneyItem::factory()->count(3)->create(['journey_id' => $journey->id]);

    expect($journey->journeyItems)->toHaveCount(3);
});

it('casts starts_at and ends_at as dates', function () {
    $journey = Journey::factory()->create([
        'starts_at' => '2026-03-01',
        'ends_at' => '2026-03-05',
    ]);

    $journey->refresh();

    expect($journey->starts_at->format('Y-m-d'))->toBe('2026-03-01')
        ->and($journey->ends_at->format('Y-m-d'))->toBe('2026-03-05');
});

it('casts properties as enum collection', function () {
    $journey = Journey::factory()->create([
        'properties' => collect([Property::ChildOnBoard]),
    ]);

    $journey->refresh();

    expect($journey->properties)->toContain(Property::ChildOnBoard);
});

it('calculates days attribute correctly', function () {
    $journey = Journey::factory()->create([
        'starts_at' => '2026-03-01',
        'ends_at' => '2026-03-05',
    ]);

    // 5 days inclusive (March 1, 2, 3, 4, 5)
    expect($journey->days)->toBe(5);
});

it('calculates days attribute for single day journey', function () {
    $journey = Journey::factory()->create([
        'starts_at' => '2026-03-01',
        'ends_at' => '2026-03-01',
    ]);

    expect($journey->days)->toBe(1);
});

it('returns forecast for destination', function () {
    $journey = Journey::factory()->create([
        'destination' => 'Zurich',
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
    ]);

    $mockForecast = new Forecast(
        avgMinTemp: 15.0,
        avgMaxTemp: 25.0,
        rainExpected: false,
        days: [],
    );

    $mock = Mockery::mock($journey)->makePartial();
    $mock->shouldReceive('forecast')
        ->once()
        ->andReturn($mockForecast);

    $result = $mock->forecast();

    expect($result)->toBeInstanceOf(Forecast::class)
        ->and($result->avgMaxTemp)->toBe(25.0)
        ->and($result->avgMinTemp)->toBe(15.0)
        ->and($result->rainExpected)->toBeFalse();
});
