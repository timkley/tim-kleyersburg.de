<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\CommandModal;
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

it('can create a quest via api', function () {
    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.quests.create'), [
        'name' => 'New Quest',
    ])->assertSuccessful();

    $this->assertDatabaseHas('quests', [
        'name' => 'New Quest',
    ]);
});
