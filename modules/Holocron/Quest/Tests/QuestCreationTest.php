<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\CommandModal\CommandModal;
use Modules\Holocron\Quest\Models\Quest;

it('can create a quest with a parent', function () {
    $parent = Quest::factory()->create();

    Livewire::test(CommandModal::class)
        ->set('name', 'Test Quest')
        ->set('payload', ['quest_id' => $parent->id])
        ->call('createQuest')
        ->assertRedirect();

    $this->assertDatabaseHas('quests', [
        'name' => 'Test Quest',
        'quest_id' => $parent->id,
    ]);
});
