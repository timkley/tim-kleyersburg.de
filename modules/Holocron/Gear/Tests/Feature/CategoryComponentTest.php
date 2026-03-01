<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Gear\Models\Category;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

it('renders the category component', function () {
    actingAs(User::factory()->create());

    $category = Category::factory()->create(['name' => 'Electronics']);

    Livewire::test('holocron.gear.categories.components.category', ['category' => $category])
        ->assertSuccessful()
        ->assertSet('name', 'Electronics');
});

it('initializes name from category model on mount', function () {
    actingAs(User::factory()->create());

    $category = Category::factory()->create(['name' => 'Clothing']);

    Livewire::test('holocron.gear.categories.components.category', ['category' => $category])
        ->assertSet('name', 'Clothing');
});

it('updates category name when property changes', function () {
    actingAs(User::factory()->create());

    $category = Category::factory()->create(['name' => 'Old Name']);

    Livewire::test('holocron.gear.categories.components.category', ['category' => $category])
        ->set('name', 'New Name');

    $category->refresh();

    expect($category->name)->toBe('New Name');
});
