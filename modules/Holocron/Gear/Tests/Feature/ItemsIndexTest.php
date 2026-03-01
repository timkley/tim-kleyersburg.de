<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Gear\Enums\Property;
use Modules\Holocron\Gear\Models\Category;
use Modules\Holocron\Gear\Models\Item;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.gear.items'))
        ->assertRedirect();
});

it('renders the items index page', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.items.index')
        ->assertSuccessful();
});

it('displays existing items', function () {
    actingAs(User::factory()->create());

    Item::factory()->create(['name' => 'Toothbrush']);

    Livewire::test('holocron.gear.items.index')
        ->assertSee('Toothbrush');
});

it('can create a new item', function () {
    actingAs(User::factory()->create());

    $category = Category::factory()->create();

    Livewire::test('holocron.gear.items.index')
        ->set('name', 'Charger')
        ->set('category_id', $category->id)
        ->set('properties', [Property::WarmWeather->value])
        ->call('submit')
        ->assertHasNoErrors();

    expect(Item::where('name', 'Charger')->exists())->toBeTrue();
});

it('validates name is required', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.items.index')
        ->set('name', '')
        ->call('submit')
        ->assertHasErrors(['name' => 'required']);
});

it('validates name minimum length', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.items.index')
        ->set('name', 'ab')
        ->call('submit')
        ->assertHasErrors(['name' => 'min']);
});

it('resets form after successful submission', function () {
    actingAs(User::factory()->create());

    $category = Category::factory()->create();

    Livewire::test('holocron.gear.items.index')
        ->set('name', 'Sunscreen')
        ->set('category_id', $category->id)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('name', '');
});

it('can delete an item', function () {
    actingAs(User::factory()->create());

    $item = Item::factory()->create();

    Livewire::test('holocron.gear.items.index')
        ->call('delete', $item->id);

    expect(Item::where('id', $item->id)->exists())->toBeFalse();
});

it('passes categories and available properties to view', function () {
    actingAs(User::factory()->create());

    Category::factory()->create(['name' => 'TestCategory']);

    Livewire::test('holocron.gear.items.index')
        ->assertViewHas('categories')
        ->assertViewHas('availableProperties');
});
