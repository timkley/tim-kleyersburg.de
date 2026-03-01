<?php

declare(strict_types=1);

use Modules\Holocron\Gear\Models\Item;
use Modules\Holocron\Gear\Models\JourneyItem;

it('can be created using factory', function () {
    $journeyItem = JourneyItem::factory()->create();

    expect($journeyItem)->toBeInstanceOf(JourneyItem::class)
        ->and($journeyItem->quantity)->toBeInt();
});

it('uses gear_journey_items table', function () {
    $journeyItem = new JourneyItem;

    expect($journeyItem->getTable())->toBe('gear_journey_items');
});

it('belongs to an item', function () {
    $item = Item::factory()->create();
    $journeyItem = JourneyItem::factory()->create(['item_id' => $item->id]);

    expect($journeyItem->item)->toBeInstanceOf(Item::class)
        ->and($journeyItem->item->id)->toBe($item->id);
});

it('casts packed_for_departure as boolean', function () {
    $journeyItem = JourneyItem::factory()->create(['packed_for_departure' => true]);

    expect($journeyItem->packed_for_departure)->toBeTrue()->toBeBool();
});

it('casts packed_for_return as boolean', function () {
    $journeyItem = JourneyItem::factory()->create(['packed_for_return' => false]);

    expect($journeyItem->packed_for_return)->toBeFalse()->toBeBool();
});
