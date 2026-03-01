<?php

declare(strict_types=1);

use App\Data\Forecast;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Journey;

it('has all expected cases', function () {
    $cases = Property::cases();

    expect($cases)->toHaveCount(4)
        ->and(collect($cases)->pluck('value')->all())->toBe([
            'warm-weather',
            'cool-weather',
            'rain-expected',
            'child-on-board',
        ]);
});

it('identifies journey applicable properties correctly', function () {
    expect(Property::ChildOnBoard->isJourneyApplicable())->toBeTrue()
        ->and(Property::WarmWeather->isJourneyApplicable())->toBeFalse()
        ->and(Property::CoolWeather->isJourneyApplicable())->toBeFalse()
        ->and(Property::RainExpected->isJourneyApplicable())->toBeFalse();
});

it('meets warm weather condition when avg max temp is above 22', function () {
    $journey = Mockery::mock(Journey::factory()->create());
    $journey->shouldReceive('forecast')
        ->once()
        ->andReturn(new Forecast(avgMinTemp: 15.0, avgMaxTemp: 25.0, rainExpected: false, days: []));

    expect(Property::WarmWeather->meetsCondition($journey))->toBeTrue();
});

it('does not meet warm weather condition when avg max temp is 22 or below', function () {
    $journey = Mockery::mock(Journey::factory()->create());
    $journey->shouldReceive('forecast')
        ->once()
        ->andReturn(new Forecast(avgMinTemp: 10.0, avgMaxTemp: 20.0, rainExpected: false, days: []));

    expect(Property::WarmWeather->meetsCondition($journey))->toBeFalse();
});

it('meets cool weather condition when avg min temp is below 8', function () {
    $journey = Mockery::mock(Journey::factory()->create());
    $journey->shouldReceive('forecast')
        ->once()
        ->andReturn(new Forecast(avgMinTemp: 5.0, avgMaxTemp: 15.0, rainExpected: false, days: []));

    expect(Property::CoolWeather->meetsCondition($journey))->toBeTrue();
});

it('does not meet cool weather condition when avg min temp is 8 or above', function () {
    $journey = Mockery::mock(Journey::factory()->create());
    $journey->shouldReceive('forecast')
        ->once()
        ->andReturn(new Forecast(avgMinTemp: 10.0, avgMaxTemp: 20.0, rainExpected: false, days: []));

    expect(Property::CoolWeather->meetsCondition($journey))->toBeFalse();
});

it('meets rain expected condition when rain is expected', function () {
    $journey = Mockery::mock(Journey::factory()->create());
    $journey->shouldReceive('forecast')
        ->once()
        ->andReturn(new Forecast(avgMinTemp: 10.0, avgMaxTemp: 20.0, rainExpected: true, days: []));

    expect(Property::RainExpected->meetsCondition($journey))->toBeTrue();
});

it('does not meet rain expected condition when no rain is expected', function () {
    $journey = Mockery::mock(Journey::factory()->create());
    $journey->shouldReceive('forecast')
        ->once()
        ->andReturn(new Forecast(avgMinTemp: 10.0, avgMaxTemp: 20.0, rainExpected: false, days: []));

    expect(Property::RainExpected->meetsCondition($journey))->toBeFalse();
});

it('meets child on board condition when journey has child property', function () {
    $journey = Journey::factory()->create([
        'properties' => collect([Property::ChildOnBoard]),
    ]);

    expect(Property::ChildOnBoard->meetsCondition($journey))->toBeTrue();
});

it('does not meet child on board condition when journey lacks child property', function () {
    $journey = Journey::factory()->create([
        'properties' => collect([]),
    ]);

    expect(Property::ChildOnBoard->meetsCondition($journey))->toBeFalse();
});
