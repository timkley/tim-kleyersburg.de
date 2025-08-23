<?php

declare(strict_types=1);

use Carbon\Carbon;
use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\DailyQuests;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Services\TemporalAwarenessService;

beforeEach(function () {
    $this->actingAs(\Modules\Holocron\User\Models\User::factory()->create());
});

test('daily quests component can be rendered', function () {
    Livewire::test(DailyQuests::class)
        ->assertStatus(200)
        ->assertSee('Add Quest for');
});

test('it displays quests for the current date by default', function () {
    $today = now()->format('Y-m-d');

    $todayQuest = Quest::factory()->create([
        'quest_date' => $today,
        'name' => 'Today Quest'
    ]);

    $yesterdayQuest = Quest::factory()->create([
        'quest_date' => now()->subDay()->format('Y-m-d'),
        'name' => 'Yesterday Quest'
    ]);

    Livewire::test(DailyQuests::class)
        ->assertSee('Today Quest')
        ->assertDontSee('Yesterday Quest');
});

test('it can navigate to different dates', function () {
    $today = now();
    $tomorrow = $today->copy()->addDay();

    $component = Livewire::test(DailyQuests::class);

    // Test next day navigation
    $component->call('nextDay')
        ->assertSet('selectedDate', $tomorrow->format('Y-m-d'));

    // Test previous day navigation
    $component->call('previousDay')
        ->assertSet('selectedDate', $today->format('Y-m-d'));

    // Test today navigation
    $component->call('today')
        ->assertSet('selectedDate', $today->format('Y-m-d'));
});

test('it can add quest for specific date', function () {
    $selectedDate = now()->format('Y-m-d');

    Livewire::test(DailyQuests::class)
        ->set('questDraft', 'Test Quest')
        ->set('selectedDate', $selectedDate)
        ->call('addQuest');

    $this->assertDatabaseHas('quests', [
        'name' => 'Test Quest',
        'quest_date' => $selectedDate,
    ]);
});

test('it can toggle quest status', function () {
    $quest = Quest::factory()->create([
        'quest_date' => now()->format('Y-m-d'),
        'status' => 'open'
    ]);

    Livewire::test(DailyQuests::class)
        ->call('toggleQuestStatus', $quest->id);

    $this->assertEquals('complete', $quest->fresh()->status->value);
});

test('temporal awareness detects tomorrow reference', function () {
    $today = now()->format('Y-m-d');
    $tomorrow = now()->addDay()->format('Y-m-d');

    Livewire::test(DailyQuests::class)
        ->set('questDraft', 'Meeting with client tomorrow')
        ->set('selectedDate', $today)
        ->call('addQuest');

    $this->assertDatabaseHas('quests', [
        'name' => 'Meeting with client tomorrow',
        'quest_date' => $tomorrow,
    ]);
});

test('temporal awareness detects specific weekday reference', function () {
    $nextMonday = now()->next('Monday')->format('Y-m-d');

    Livewire::test(DailyQuests::class)
        ->set('questDraft', 'Team standup next Monday')
        ->call('addQuest');

    $this->assertDatabaseHas('quests', [
        'name' => 'Team standup next Monday',
        'quest_date' => $nextMonday,
    ]);
});

test('temporal awareness service parses various date formats', function () {
    $service = new TemporalAwarenessService();
    $baseDate = Carbon::create(2025, 8, 22); // Friday

    // Test tomorrow
    $result = $service->extractPrimaryDate('Meeting tomorrow', $baseDate);
    expect($result?->format('Y-m-d'))->toBe('2025-08-23');

    // Test next Monday
    $result = $service->extractPrimaryDate('Presentation next Monday', $baseDate);
    expect($result?->format('Y-m-d'))->toBe('2025-08-25');

    // Test specific date
    $result = $service->extractPrimaryDate('Deadline on 12/25/2025', $baseDate);
    expect($result?->format('Y-m-d'))->toBe('2025-12-25');

    // Test in X days
    $result = $service->extractPrimaryDate('Follow up in 3 days', $baseDate);
    expect($result?->format('Y-m-d'))->toBe('2025-08-25');
});

test('temporal awareness handles no date references', function () {
    $service = new TemporalAwarenessService();

    $result = $service->extractPrimaryDate('Regular task with no date');
    expect($result)->toBeNull();
});

test('component updates selected date when temporal awareness detects different date', function () {
    $today = now()->format('Y-m-d');
    $tomorrow = now()->addDay()->format('Y-m-d');

    $component = Livewire::test(DailyQuests::class)
        ->set('questDraft', 'Call client tomorrow')
        ->set('selectedDate', $today)
        ->call('addQuest')
        ->assertSet('selectedDate', $tomorrow);
});

test('component displays empty state when no quests exist', function () {
    Livewire::test(DailyQuests::class)
        ->assertSee('No quests for')
        ->assertSee('Add your first quest above to get started!');
});

test('component validates quest draft is required', function () {
    Livewire::test(DailyQuests::class)
        ->set('questDraft', '')
        ->call('addQuest')
        ->assertHasErrors(['questDraft' => 'required']);
});

test('component validates quest draft minimum length', function () {
    Livewire::test(DailyQuests::class)
        ->set('questDraft', 'ab')
        ->call('addQuest')
        ->assertHasErrors(['questDraft' => 'min']);
});
