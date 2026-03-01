<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Modules\Holocron\Gear\Models\Item;
use Modules\Holocron\Gear\Models\Journey;
use Modules\Holocron\Gear\Models\JourneyItem;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [['latitude' => 52.52, 'longitude' => 13.41]],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => ['2026-06-01'],
                'temperature_2m_max' => [25.0],
                'temperature_2m_min' => [15.0],
                'precipitation_sum' => [0],
                'weather_code' => [0],
            ],
        ]),
        'archive-api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => ['2026-06-01'],
                'temperature_2m_max' => [25.0],
                'temperature_2m_min' => [15.0],
                'precipitation_sum' => [0],
                'weather_code' => [0],
            ],
        ]),
    ]);
});

it('is not reachable when unauthenticated', function () {
    $journey = Journey::factory()->create();

    get(route('holocron.gear.journeys.show', $journey))
        ->assertRedirect();
});

it('renders the journey show page', function () {
    actingAs(User::factory()->create());

    $journey = Journey::factory()->create();

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->assertSuccessful();
});

it('displays journey items grouped by category', function () {
    actingAs(User::factory()->create());

    $journey = Journey::factory()->create();
    $item = Item::factory()->create(['name' => 'Sunglasses']);
    JourneyItem::factory()->create([
        'journey_id' => $journey->id,
        'item_id' => $item->id,
    ]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->assertViewHas('groups');
});

it('can generate a packlist for the journey', function () {
    actingAs(User::factory()->create());

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
    ]);

    // Create items without properties (should always be included)
    Item::factory()->count(3)->create(['properties' => []]);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(3);
});

it('clears existing journey items when regenerating packlist', function () {
    actingAs(User::factory()->create());

    $journey = Journey::factory()->create([
        'starts_at' => '2026-06-01',
        'ends_at' => '2026-06-05',
    ]);

    // Create 2 items that will be used for the new packlist
    $item1 = Item::factory()->create(['properties' => []]);
    $item2 = Item::factory()->create(['properties' => []]);

    // Manually create 5 old journey items pointing to these items
    JourneyItem::factory()->count(3)->create(['journey_id' => $journey->id, 'item_id' => $item1->id]);
    JourneyItem::factory()->count(2)->create(['journey_id' => $journey->id, 'item_id' => $item2->id]);

    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(5);

    Livewire::test('holocron.gear.journeys.show', ['journey' => $journey])
        ->call('generatePacklist', $journey);

    // After regeneration, only 2 items should exist (one per Item)
    expect(JourneyItem::where('journey_id', $journey->id)->count())->toBe(2);
});
