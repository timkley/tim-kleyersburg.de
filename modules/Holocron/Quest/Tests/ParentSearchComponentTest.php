<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\Components\ParentSearch;
use Modules\Holocron\Quest\Models\Quest;

it('renders parent search component', function () {
    Livewire::test(ParentSearch::class)
        ->assertStatus(200);
});

it('starts with empty search', function () {
    Livewire::test(ParentSearch::class)
        ->assertSet('searchTerm', '')
        ->assertSet('quests', []);
});

it('clears quests when search term is emptied', function () {
    Quest::factory()->create(['name' => 'Test Quest']);

    Livewire::test(ParentSearch::class)
        ->set('searchTerm', '')
        ->assertSet('quests', []);
});

it('searches quests when search term is provided', function () {
    config(['scout.driver' => 'collection']);

    Quest::factory()->create(['name' => 'Build feature']);
    Quest::factory()->create(['name' => 'Fix bug']);

    Livewire::test(ParentSearch::class)
        ->set('searchTerm', 'Build')
        ->assertSuccessful();
});
