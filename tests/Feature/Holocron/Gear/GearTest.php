<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.gear'))
        ->assertRedirect();
});

it('works', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.gear')
        ->assertSuccessful();
});

it('can show all categories', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.gear.categories.index')
        ->assertSuccessful();
});
