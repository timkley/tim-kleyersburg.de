<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Tests\Feature\Livewire\Components;

use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\SearchModal;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

it('renders the search modal component', function () {
    Livewire::test(SearchModal::class)
        ->assertOk()
        ->assertSee('Quests suchen...');
});

it('has an empty query by default', function () {
    Livewire::test(SearchModal::class)
        ->assertSet('query', '')
        ->assertSet('includeCompleted', false);
});

it('does not perform a search when query is empty', function () {
    Livewire::test(SearchModal::class)
        ->assertViewHas('results', null);
});

it('performs a search when query is set', function () {
    Livewire::test(SearchModal::class)
        ->set('query', 'test quest')
        ->assertViewHas('results');
});

it('can toggle the include completed checkbox', function () {
    Livewire::test(SearchModal::class)
        ->assertSet('includeCompleted', false)
        ->set('includeCompleted', true)
        ->assertSet('includeCompleted', true);
});

it('shows the include completed checkbox', function () {
    Livewire::test(SearchModal::class)
        ->assertSee('Abgeschlossene Quests einbeziehen');
});
