<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\DailyQuest;
use Modules\Holocron\Quest\Models\Quest;

uses(RefreshDatabase::class);

it('creates a daily quest for today if one does not exist', function () {
    Livewire::test(DailyQuest::class)
        ->assertSet('date', today()->format('Y-m-d'))
        ->assertNotSet('quest', null)
        ->assertSee(today()->format('d. F'));
});

it('loads existing daily quest', function () {
    $quest = Quest::factory()->create(['date' => today()->toDateString()]);

    Livewire::test(DailyQuest::class)
        ->assertSet('quest.id', $quest->id);
});

it('can navigate to the next day', function () {
    Livewire::test(DailyQuest::class)
        ->call('nextDay')
        ->assertSet('date', today()->addDay()->format('Y-m-d'))
        ->assertNotSet('quest', null)
        ->assertSee(today()->addDay()->format('d. F'));
});

it('can navigate to the previous day', function () {
    Livewire::test(DailyQuest::class)
        ->call('previousDay')
        ->assertSet('date', today()->subDay()->format('Y-m-d'))
        ->assertNotSet('quest', null)
        ->assertSee(today()->subDay()->format('d. F'));
});

it('can navigate to today', function () {
    Livewire::test(DailyQuest::class)
        ->call('nextDay')
        ->call('goToToday')
        ->assertSet('date', today()->format('Y-m-d'))
        ->assertNotSet('quest', null)
        ->assertSee(today()->format('d. F'));
});

it('can reschedule a sub-quest', function () {
    $todayQuest = Quest::factory()->create(['date' => today()->toDateString()]);
    $tomorrowQuest = Quest::factory()->create(['date' => today()->addDay()->toDateString()]);
    $subQuest = Quest::factory()->create(['quest_id' => $todayQuest->id]);

    Livewire::test(DailyQuest::class)
        ->call('rescheduleSubQuest', $subQuest->id, today()->addDay()->toDateString());

    $this->assertDatabaseHas('quests', [
        'id' => $subQuest->id,
        'quest_id' => $tomorrowQuest->id,
    ]);
});

it('displays uncompleted sub-quests from the past', function () {
    $yesterdayQuest = Quest::factory()->create(['date' => today()->subDay()->toDateString()]);
    $subQuest = Quest::factory()->create(['quest_id' => $yesterdayQuest->id, 'status' => 'open']);

    Livewire::test(DailyQuest::class)
        ->assertSee($subQuest->name);
});

it('updates the quest when the date is changed', function () {
    $todayQuest = Quest::factory()->create(['date' => today()->toDateString()]);
    $tomorrowQuest = Quest::factory()->create(['date' => today()->addDay()->toDateString()]);

    Livewire::test(DailyQuest::class)
        ->assertSet('quest.id', $todayQuest->id)
        ->call('nextDay')
        ->assertSet('quest.id', $tomorrowQuest->id);
});

it('formats the date in the url', function () {
    $component = Livewire::test(DailyQuest::class)
        ->call('nextDay');

    $this->assertStringContainsString('date='.today()->addDay()->format('Y-m-d'), $component->snapshot['memo']['path']);
});