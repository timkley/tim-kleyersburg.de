<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Gear\Models\Category;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.gear.categories'))
        ->assertRedirect();
});

it('renders the categories index page', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.categories.index')
        ->assertSuccessful();
});

it('displays existing categories', function () {
    actingAs(User::factory()->create());

    $category = Category::factory()->create(['name' => 'Electronics']);

    Livewire::test('holocron.gear.categories.index')
        ->assertSee('Electronics');
});

it('can create a new category', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.categories.index')
        ->set('name', 'Clothing')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Category::where('name', 'Clothing')->exists())->toBeTrue();
});

it('validates name is required', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.categories.index')
        ->set('name', '')
        ->call('submit')
        ->assertHasErrors(['name' => 'required']);
});

it('validates name minimum length', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.categories.index')
        ->set('name', 'ab')
        ->call('submit')
        ->assertHasErrors(['name' => 'min']);
});

it('validates name maximum length', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.categories.index')
        ->set('name', str_repeat('a', 256))
        ->call('submit')
        ->assertHasErrors(['name' => 'max']);
});

it('resets form after successful submission', function () {
    actingAs(User::factory()->create());

    Livewire::test('holocron.gear.categories.index')
        ->set('name', 'Toiletries')
        ->call('submit')
        ->assertSet('name', '');
});

it('can delete a category', function () {
    actingAs(User::factory()->create());

    $category = Category::factory()->create();

    Livewire::test('holocron.gear.categories.index')
        ->call('delete', $category->id);

    expect(Category::where('id', $category->id)->exists())->toBeFalse();
});
