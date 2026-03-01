<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\Components\MainQuests;
use Modules\Holocron\Quest\Livewire\Components\NextQuests;
use Modules\Holocron\Quest\Livewire\Components\TodaysQuests;
use Modules\Holocron\Quest\Models\Quest;

// --- TodaysQuests ---

it('renders todays quests component', function () {
    Livewire::test(TodaysQuests::class)
        ->assertStatus(200);
});

it('displays quests accepted for today', function () {
    $quest = Quest::factory()->create([
        'date' => today(),
        'daily' => false,
        'completed_at' => null,
    ]);

    Livewire::test(TodaysQuests::class)
        ->assertSee($quest->name);
});

it('displays quests from the past that are not completed', function () {
    $quest = Quest::factory()->create([
        'date' => today()->subDays(3),
        'daily' => false,
        'completed_at' => null,
    ]);

    Livewire::test(TodaysQuests::class)
        ->assertSee($quest->name);
});

it('does not display completed quests', function () {
    $quest = Quest::factory()->create([
        'date' => today(),
        'daily' => false,
        'completed_at' => now(),
    ]);

    Livewire::test(TodaysQuests::class)
        ->assertDontSee($quest->name);
});

it('does not display daily quests', function () {
    $quest = Quest::factory()->create([
        'date' => today(),
        'daily' => true,
        'completed_at' => null,
    ]);

    Livewire::test(TodaysQuests::class)
        ->assertDontSee($quest->name);
});

it('does not display future quests', function () {
    $quest = Quest::factory()->create([
        'date' => today()->addDays(5),
        'daily' => false,
        'completed_at' => null,
    ]);

    Livewire::test(TodaysQuests::class)
        ->assertDontSee($quest->name);
});

// --- NextQuests ---

it('renders next quests component', function () {
    Livewire::test(NextQuests::class)
        ->assertStatus(200);
});

it('displays unplanned quests without children', function () {
    $quest = Quest::factory()->create([
        'date' => null,
        'daily' => false,
        'completed_at' => null,
        'is_note' => false,
    ]);

    Livewire::test(NextQuests::class)
        ->assertSee($quest->name);
});

it('does not display completed quests in next quests', function () {
    $quest = Quest::factory()->create([
        'date' => null,
        'daily' => false,
        'completed_at' => now(),
        'is_note' => false,
    ]);

    Livewire::test(NextQuests::class)
        ->assertDontSee($quest->name);
});

it('does not display quests with date in next quests', function () {
    $quest = Quest::factory()->create([
        'date' => today(),
        'daily' => false,
        'completed_at' => null,
        'is_note' => false,
    ]);

    Livewire::test(NextQuests::class)
        ->assertDontSee($quest->name);
});

it('does not display note quests in next quests', function () {
    $quest = Quest::factory()->create([
        'date' => null,
        'daily' => false,
        'completed_at' => null,
        'is_note' => true,
    ]);

    Livewire::test(NextQuests::class)
        ->assertDontSee($quest->name);
});

it('does not display quests with uncompleted children', function () {
    $parent = Quest::factory()->create([
        'date' => null,
        'daily' => false,
        'completed_at' => null,
        'is_note' => false,
    ]);
    Quest::factory()->create([
        'quest_id' => $parent->id,
        'completed_at' => null,
    ]);

    Livewire::test(NextQuests::class)
        ->assertDontSee($parent->name);
});

// --- MainQuests ---

it('renders main quests component', function () {
    Livewire::test(MainQuests::class)
        ->assertStatus(200);
});

it('displays note quests without parents', function () {
    $quest = Quest::factory()->create([
        'quest_id' => null,
        'is_note' => true,
    ]);

    Livewire::test(MainQuests::class)
        ->assertSee($quest->name);
});

it('does not display non-note quests', function () {
    $quest = Quest::factory()->create([
        'quest_id' => null,
        'is_note' => false,
    ]);

    Livewire::test(MainQuests::class)
        ->assertDontSee($quest->name);
});

it('does not display note quests with parents', function () {
    $parent = Quest::factory()->create();
    $quest = Quest::factory()->create([
        'quest_id' => $parent->id,
        'is_note' => true,
    ]);

    Livewire::test(MainQuests::class)
        ->assertDontSee($quest->name);
});
