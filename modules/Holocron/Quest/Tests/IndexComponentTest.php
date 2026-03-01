<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\Index;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('renders the index page', function () {
    Livewire::test(Index::class)
        ->assertStatus(200);
});

it('requires authentication', function () {
    auth()->logout();

    $this->get(route('holocron.quests'))
        ->assertRedirect();
});
