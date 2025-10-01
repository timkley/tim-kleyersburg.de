<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Holocron\Quest\Jobs\RecurQuests;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

uses(RefreshDatabase::class);

it('recurs daily quests', function () {
    $masterQuest = Quest::factory()->create();
    $recurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
        'last_recurred_at' => now()->subDay(),
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 2);
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});

it('does not recur if not due', function () {
    $masterQuest = Quest::factory()->create();
    QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
        'last_recurred_at' => now(),
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 1);
});

it('does not recur if previous instance is not completed', function () {
    $masterQuest = Quest::factory()->create();
    $recurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
        'last_recurred_at' => now()->subDay(),
    ]);
    Quest::factory()->create([
        'created_from_recurrence_id' => $recurrence->id,
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 2);
});

it('recurs daily quests regardless of time difference', function () {
    $masterQuest = Quest::factory()->create();

    // Simulate last recurrence at 10:00 AM yesterday
    $lastRecurredAt = now()->subDay()->setHour(10)->setMinute(0)->setSecond(0);

    $recurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
        'last_recurred_at' => $lastRecurredAt,
    ]);

    // Travel to 8:00 AM today (2 hours earlier than last recurrence time)
    $this->travel(now()->setHour(8)->setMinute(0)->setSecond(0));

    (new RecurQuests)->handle();

    // Should create a new quest instance despite the time difference
    $this->assertDatabaseCount('quests', 2);
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});

it('recurs quests with custom intervals', function () {
    $masterQuest = Quest::factory()->create();
    $recurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 7, // Every 7 days (weekly)
        'last_recurred_at' => now()->subDays(7),
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 2);
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});

it('does not recur with custom intervals if not due', function () {
    $masterQuest = Quest::factory()->create();
    QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 30, // Every 30 days (monthly-ish)
        'last_recurred_at' => now()->subDays(15), // Only 15 days ago
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 1); // No new quest created
});
