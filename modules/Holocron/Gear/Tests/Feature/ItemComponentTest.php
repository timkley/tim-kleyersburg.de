<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Category;
use Modules\Holocron\Gear\Models\Item;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

it('renders the item component', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create(['name' => 'Laptop']);

    Livewire::test('holocron.gear.items.components.item', ['item' => $item])
        ->assertSuccessful()
        ->assertSet('name', 'Laptop');
});

it('initializes all properties from item model on mount', function () {
    actingAs(User::factory()->create());

    $category = Category::factory()->create();
    $item = Item::factory()->create([
        'name' => 'Camera',
        'category_id' => $category->id,
        'quantity' => 2,
        'quantity_per_day' => 0.5,
        'properties' => [Property::WarmWeather],
    ]);

    Livewire::test('holocron.gear.items.components.item', ['item' => $item])
        ->assertSet('name', 'Camera')
        ->assertSet('category_id', $category->id)
        ->assertSet('quantity', 2)
        ->assertSet('quantity_per_day', 0.5);
});

it('updates item name when property changes', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create(['name' => 'Old Name']);

    Livewire::test('holocron.gear.items.components.item', ['item' => $item])
        ->set('name', 'New Name');

    $item->refresh();

    expect($item->name)->toBe('New Name');
});

it('updates item quantity when property changes', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create(['quantity' => 1]);

    Livewire::test('holocron.gear.items.components.item', ['item' => $item])
        ->set('quantity', 5);

    $item->refresh();

    expect($item->quantity)->toBe(5);
});

it('updates item category when property changes', function () {
    actingAs(User::factory()->create());

    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $item = Item::factory()->create(['category_id' => $category1->id]);

    Livewire::test('holocron.gear.items.components.item', ['item' => $item])
        ->set('category_id', $category2->id);

    $item->refresh();

    expect($item->category_id)->toBe($category2->id);
});

it('sets category_id to empty string when item has no category', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create(['category_id' => null]);

    Livewire::test('holocron.gear.items.components.item', ['item' => $item])
        ->assertSet('category_id', '');
});

it('passes categories and available properties to view', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create();

    Livewire::test('holocron.gear.items.components.item', ['item' => $item])
        ->assertViewHas('categories')
        ->assertViewHas('availableProperties');
});

it('updates item properties when a properties field changes', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create(['properties' => []]);

    Livewire::test('holocron.gear.items.components.item', ['item' => $item])
        ->set('properties', collect([Property::WarmWeather]));

    $item->refresh();

    expect($item->properties)->toContain(Property::WarmWeather);
});
