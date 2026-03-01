<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Item;
use Modules\Holocron\Gear\Models\Journey;
use Modules\Holocron\Gear\Models\JourneyItem;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

function fakeWeatherApi(float $maxTemp = 25.0, float $minTemp = 15.0, float $rain = 0): void
{
    Cache::flush();

    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [['latitude' => 52.52, 'longitude' => 13.41]],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => ['2026-06-01'],
                'temperature_2m_max' => [$maxTemp],
                'temperature_2m_min' => [$minTemp],
                'precipitation_sum' => [$rain],
                'weather_code' => [0],
            ],
        ]),
        'archive-api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => ['2026-06-01'],
                'temperature_2m_max' => [$maxTemp],
                'temperature_2m_min' => [$minTemp],
                'precipitation_sum' => [$rain],
                'weather_code' => [0],
            ],
        ]),
    ]);
}

it('includes items without properties in packlist', function () {
    actingAs(User::factory()->create());
    fakeWeatherApi();

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
    ]);
    Item::factory()->create(['properties' => [], 'quantity' => 1]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(1);
});

it('includes items with null properties in packlist', function () {
    actingAs(User::factory()->create());
    fakeWeatherApi();

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
    ]);
    Item::factory()->create(['properties' => null, 'quantity' => 1]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(1);
});

it('uses fixed quantity when item has a quantity set', function () {
    actingAs(User::factory()->create());
    fakeWeatherApi();

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
    ]);
    Item::factory()->create(['properties' => [], 'quantity' => 3, 'quantity_per_day' => 0]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    $journeyItem = JourneyItem::where('journey_id', $journey->id)->first();
    expect($journeyItem->quantity)->toBe(3);
});

it('calculates quantity based on days when quantity is zero', function () {
    actingAs(User::factory()->create());
    fakeWeatherApi();

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05', // 5 days
    ]);
    // quantity_per_day = 1.0, 0 fixed quantity
    // Formula: max(ceil(1.0 * 5) - 1, 1) = max(4, 1) = 4
    Item::factory()->create(['properties' => [], 'quantity' => 0, 'quantity_per_day' => 1.0]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    $journeyItem = JourneyItem::where('journey_id', $journey->id)->first();
    expect($journeyItem->quantity)->toBe(4);
});

it('ensures minimum quantity of 1 for per-day items', function () {
    actingAs(User::factory()->create());
    fakeWeatherApi();

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-01', // 1 day
    ]);
    // Formula: max(ceil(0.5 * 1) - 1, 1) = max(0, 1) = 1
    Item::factory()->create(['properties' => [], 'quantity' => 0, 'quantity_per_day' => 0.5]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    $journeyItem = JourneyItem::where('journey_id', $journey->id)->first();
    expect($journeyItem->quantity)->toBe(1);
});

it('excludes items when weather property conditions are not met', function () {
    actingAs(User::factory()->create());

    // Cold weather: avgMaxTemp 15 <= 22 so WarmWeather condition fails
    fakeWeatherApi(maxTemp: 15.0, minTemp: 5.0);

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
    ]);

    // Item with warm weather property
    $warmItem = Item::factory()->create([
        'name' => 'Sunscreen',
        'properties' => [Property::WarmWeather],
        'quantity' => 1,
    ]);

    // Item without properties (always included)
    $genericItem = Item::factory()->create([
        'name' => 'Toothbrush',
        'properties' => [],
        'quantity' => 1,
    ]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    // Warm weather item should NOT be included (avgMaxTemp 15 <= 22)
    $journeyItems = JourneyItem::where('journey_id', $journey->id)->pluck('item_id');
    expect($journeyItems)->not->toContain($warmItem->id)
        ->and($journeyItems)->toContain($genericItem->id);
});

it('includes items when weather property conditions are met', function () {
    actingAs(User::factory()->create());

    // Warm weather: avgMaxTemp 30 > 22 so WarmWeather condition passes
    fakeWeatherApi(maxTemp: 30.0, minTemp: 20.0);

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
    ]);

    $warmItem = Item::factory()->create([
        'name' => 'Sunscreen',
        'properties' => [Property::WarmWeather],
        'quantity' => 1,
    ]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    $journeyItems = JourneyItem::where('journey_id', $journey->id)->pluck('item_id');
    expect($journeyItems)->toContain($warmItem->id);
});

it('includes child items when journey has child on board property', function () {
    actingAs(User::factory()->create());
    fakeWeatherApi();

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
        'properties' => collect([Property::ChildOnBoard]),
    ]);

    $childItem = Item::factory()->create([
        'name' => 'Baby Wipes',
        'properties' => [Property::ChildOnBoard],
        'quantity' => 1,
    ]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    $journeyItems = JourneyItem::where('journey_id', $journey->id)->pluck('item_id');
    expect($journeyItems)->toContain($childItem->id);
});

it('handles empty packlist when no items match', function () {
    actingAs(User::factory()->create());

    // Cold weather so WarmWeather items are excluded
    fakeWeatherApi(maxTemp: 15.0, minTemp: 5.0);

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
        'properties' => collect([]),
    ]);

    // Only create items with properties that won't be met
    Item::factory()->create([
        'properties' => [Property::WarmWeather],
        'quantity' => 1,
    ]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(0);
});

it('excludes child items when journey does not have child on board', function () {
    actingAs(User::factory()->create());
    fakeWeatherApi();

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
        'properties' => collect([]),
    ]);

    $childItem = Item::factory()->create([
        'name' => 'Baby Wipes',
        'properties' => [Property::ChildOnBoard],
        'quantity' => 1,
    ]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    $journeyItems = JourneyItem::where('journey_id', $journey->id)->pluck('item_id');
    expect($journeyItems)->not->toContain($childItem->id);
});
