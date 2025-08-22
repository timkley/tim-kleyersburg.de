<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.gear'))
        ->assertRedirect();
});

it('works', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.gear.index')
        ->assertSuccessful();
});

it('can show all categories', function () {
    $user = User::factory()->create();
    actingAs($user);

    Livewire::test('holocron.gear.categories.index')
        ->assertSuccessful();
});
