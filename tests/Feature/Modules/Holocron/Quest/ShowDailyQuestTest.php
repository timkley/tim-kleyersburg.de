<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\DailyQuest;
use Modules\Holocron\Quest\Models\Quest;

uses(RefreshDatabase::class);

it('creates a daily quest for today if one does not exist', function () {
    Livewire::test(DailyQuest::class)
        ->assertSet('date', today()->format('Y-m-d'))
        ->assertSee(today()->format('d. F'));
});

it('loads existing daily quest', function () {
    $quest = Quest::factory()->create(['date' => today()->toDateString()]);

    Livewire::test(DailyQuest::class)
        ->assertSee($quest->name);
});

it('can navigate to the next day', function () {
    Livewire::test(DailyQuest::class)
        ->call('nextDay')
        ->assertSet('date', today()->addDay()->format('Y-m-d'));
});

it('can navigate to the previous day', function () {
    Livewire::test(DailyQuest::class)
        ->call('previousDay')
        ->assertSet('date', today()->subDay()->format('Y-m-d'));
});

it('can navigate to today', function () {
    Livewire::test(DailyQuest::class)
        ->call('nextDay')
        ->call('goToToday')
        ->assertSet('date', today()->format('Y-m-d'));
});

it('displays uncompleted sub-quests from the past', function () {
    $yesterdayQuest = Quest::factory()->create(['date' => today()->subDay()->toDateString()]);
    $subQuest = Quest::factory()->create(['quest_id' => $yesterdayQuest->id, 'status' => 'open']);

    Livewire::test(DailyQuest::class)
        ->assertSee($subQuest->name);
});
