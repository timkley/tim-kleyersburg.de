<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\Components\Item;
use Modules\Holocron\Quest\Models\Quest;

it('renders an incomplete quest item', function () {
    $quest = Quest::factory()->create([
        'completed_at' => null,
        'is_note' => false,
    ]);

    Livewire::test(Item::class, ['quest' => $quest])
        ->assertStatus(200)
        ->assertSee($quest->name);
});

it('renders a completed quest item', function () {
    $quest = Quest::factory()->create([
        'completed_at' => now(),
        'is_note' => false,
    ]);

    Livewire::test(Item::class, ['quest' => $quest])
        ->assertStatus(200)
        ->assertSee($quest->name);
});

it('toggles quest complete', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    Livewire::test(Item::class, ['quest' => $quest])
        ->call('toggleComplete');

    expect($quest->fresh()->completed_at)->not->toBeNull();
});

it('toggles quest accept', function () {
    $quest = Quest::factory()->create(['date' => null]);

    Livewire::test(Item::class, ['quest' => $quest])
        ->call('toggleAccept')
        ->assertDispatched('quest:accepted');

    expect($quest->fresh()->date)->not->toBeNull();
});

it('prints a quest', function () {
    $quest = Quest::factory()->create(['should_be_printed' => false]);

    Livewire::test(Item::class, ['quest' => $quest])
        ->call('print');

    expect((bool) $quest->fresh()->should_be_printed)->toBeTrue();
});

it('deletes a quest', function () {
    $quest = Quest::factory()->create();

    Livewire::test(Item::class, ['quest' => $quest])
        ->call('deleteQuest', $quest->id)
        ->assertDispatched('quest:deleted');

    expect(Quest::find($quest->id))->toBeNull();
});

it('shows parent link when showParent is true', function () {
    $parent = Quest::factory()->create(['name' => 'Parent Quest']);
    $quest = Quest::factory()->create(['quest_id' => $parent->id]);

    Livewire::test(Item::class, ['quest' => $quest, 'showParent' => true])
        ->assertSee('Parent Quest');
});

it('hides parent link when showParent is false', function () {
    $parent = Quest::factory()->create(['name' => 'Parent Quest']);
    $quest = Quest::factory()->create(['quest_id' => $parent->id]);

    Livewire::test(Item::class, ['quest' => $quest, 'showParent' => false])
        ->assertDontSee('Parent Quest');
});

it('shows children count', function () {
    $quest = Quest::factory()->create();
    Quest::factory()->count(3)->create(['quest_id' => $quest->id]);
    $quest->load('children');

    Livewire::test(Item::class, ['quest' => $quest])
        ->assertSee('3');
});
