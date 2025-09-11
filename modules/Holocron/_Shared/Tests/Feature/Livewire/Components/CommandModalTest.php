<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Tests\Feature\Livewire\Components;

use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\CommandModal;
use Modules\Holocron\Quest\Models\Quest;

use function Pest\Laravel\assertDatabaseHas;

it('can create a quest and redirect', function () {
    Livewire::test(CommandModal::class)
        ->set('name', 'My new quest')
        ->call('createQuest', false)
        ->assertRedirect(route('holocron.quests.show', Quest::first()));

    assertDatabaseHas('quests', [
        'name' => 'My new quest',
    ]);
});

it('can create a quest and stay open', function () {
    Livewire::test(CommandModal::class)
        ->set('name', 'Another quest')
        ->call('createQuest', true)
        ->assertSet('name', '')
        ->assertNoRedirect();

    assertDatabaseHas('quests', [
        'name' => 'Another quest',
    ]);
});
