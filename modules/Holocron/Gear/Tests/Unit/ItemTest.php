<?php

declare(strict_types=1);

use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Category;
use Modules\Holocron\Gear\Models\Item;

it('can be created using factory', function () {
    $item = Item::factory()->create();

    expect($item)->toBeInstanceOf(Item::class)
        ->and($item->name)->toBeString()
        ->and($item->quantity)->toBeInt()
        ->and($item->quantity_per_day)->toBeFloat();
});

it('uses gear_items table', function () {
    $item = new Item;

    expect($item->getTable())->toBe('gear_items');
});

it('belongs to a category', function () {
    $category = Category::factory()->create();
    $item = Item::factory()->create(['category_id' => $category->id]);

    expect($item->category)->toBeInstanceOf(Category::class)
        ->and($item->category->id)->toBe($category->id);
});

it('can have a null category', function () {
    $item = Item::factory()->create(['category_id' => null]);

    expect($item->category)->toBeNull();
});

it('casts properties as enum collection', function () {
    $item = Item::factory()->create([
        'properties' => [Property::WarmWeather, Property::RainExpected],
    ]);

    $item->refresh();

    expect($item->properties)->toHaveCount(2)
        ->and($item->properties)->toContain(Property::WarmWeather)
        ->and($item->properties)->toContain(Property::RainExpected);
});

it('can have empty properties', function () {
    $item = Item::factory()->create(['properties' => []]);

    $item->refresh();

    expect($item->properties)->toBeEmpty();
});
