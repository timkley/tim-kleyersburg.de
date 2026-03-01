<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Gear\Models\Item;
use Modules\Holocron\Gear\Models\JourneyItem;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

it('renders the journey item component', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create(['name' => 'Passport']);
    $journeyItem = JourneyItem::factory()->create(['item_id' => $item->id]);

    Livewire::test('holocron.gear.journeys.components.journey-item', ['journeyItem' => $journeyItem])
        ->assertSuccessful()
        ->assertSet('name', 'Passport');
});

it('initializes properties from journey item on mount', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create(['name' => 'Charger']);
    $journeyItem = JourneyItem::factory()->create([
        'item_id' => $item->id,
        'packed_for_departure' => true,
        'packed_for_return' => false,
    ]);

    Livewire::test('holocron.gear.journeys.components.journey-item', ['journeyItem' => $journeyItem])
        ->assertSet('name', 'Charger')
        ->assertSet('packed_for_departure', true)
        ->assertSet('packed_for_return', false);
});

it('updates packed_for_departure on the model', function () {
    actingAs(User::factory()->create());

    $journeyItem = JourneyItem::factory()->create(['packed_for_departure' => false]);

    Livewire::test('holocron.gear.journeys.components.journey-item', ['journeyItem' => $journeyItem])
        ->set('packed_for_departure', true);

    $journeyItem->refresh();

    expect($journeyItem->packed_for_departure)->toBeTrue();
});

it('updates packed_for_return on the model', function () {
    actingAs(User::factory()->create());

    $journeyItem = JourneyItem::factory()->create(['packed_for_return' => false]);

    Livewire::test('holocron.gear.journeys.components.journey-item', ['journeyItem' => $journeyItem])
        ->set('packed_for_return', true);

    $journeyItem->refresh();

    expect($journeyItem->packed_for_return)->toBeTrue();
});

it('can delete a journey item and dispatches event', function () {
    actingAs(User::factory()->create());

    $journeyItem = JourneyItem::factory()->create();

    Livewire::test('holocron.gear.journeys.components.journey-item', ['journeyItem' => $journeyItem])
        ->call('delete')
        ->assertDispatched('journey-item:deleted');

    expect(JourneyItem::where('id', $journeyItem->id)->exists())->toBeFalse();
});
