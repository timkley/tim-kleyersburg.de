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

it('recurs completion-based tasks after master completion when interval elapsed', function () {
    $masterQuest = Quest::factory()->create([
        'completed_at' => now()->subDay(),
    ]);
    $recurrence = QuestRecurrence::factory()->completionBased()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 2);
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});

it('does not recur completion-based tasks until a completion exists', function () {
    $masterQuest = Quest::factory()->create(); // Not completed
    QuestRecurrence::factory()->completionBased()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 1); // No new quest created
});

it('does not recur completion-based when previous child is not completed', function () {
    $masterQuest = Quest::factory()->create([
        'completed_at' => now()->subDays(2),
    ]);
    $recurrence = QuestRecurrence::factory()->completionBased()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
    ]);

    // Create an uncompleted child
    Quest::factory()->create([
        'created_from_recurrence_id' => $recurrence->id,
        'completed_at' => null,
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 2); // No new quest created
});

it('respects interval for completion-based regardless of time-of-day', function () {
    $masterQuest = Quest::factory()->create([
        'completed_at' => now()->subDay()->setHour(22)->setMinute(0), // Yesterday 22:00
    ]);
    $recurrence = QuestRecurrence::factory()->completionBased()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
    ]);

    // Travel to 08:00 today
    $this->travel(now()->setHour(8)->setMinute(0)->setSecond(0));

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 2);
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});

it('handles mixed recurrence types in one run', function () {
    // Recurrence-based (due)
    $masterQuest1 = Quest::factory()->create();
    $recurrenceRecurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest1->id,
        'every_x_days' => 1,
        'recurrence_type' => QuestRecurrence::TYPE_RECURRENCE_BASED,
        'last_recurred_at' => now()->subDay(),
    ]);

    // Completion-based (not due - no completion)
    $masterQuest2 = Quest::factory()->create();
    $completionRecurrence = QuestRecurrence::factory()->completionBased()->create([
        'quest_id' => $masterQuest2->id,
        'every_x_days' => 1,
    ]);

    (new RecurQuests)->handle();

    // Only the recurrence-based should have created a new quest
    $this->assertDatabaseCount('quests', 3); // 2 masters + 1 new child
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest1->name,
        'created_from_recurrence_id' => $recurrenceRecurrence->id,
    ]);
    $this->assertDatabaseMissing('quests', [
        'created_from_recurrence_id' => $completionRecurrence->id,
    ]);
});

it('supports switching from recurrence-based to completion-based', function () {
    $masterQuest = Quest::factory()->create([
        'completed_at' => now()->subDays(2),
    ]);
    $recurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
        'recurrence_type' => QuestRecurrence::TYPE_RECURRENCE_BASED,
        'last_recurred_at' => now()->subDays(3), // Due based on recurrence
    ]);

    // Switch to completion-based
    $recurrence->update(['recurrence_type' => QuestRecurrence::TYPE_COMPLETION_BASED]);

    (new RecurQuests)->handle();

    // Should create a quest based on completion (2 days ago + 1 day = due)
    $this->assertDatabaseCount('quests', 2);
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});

it('supports switching from completion-based to recurrence-based', function () {
    $masterQuest = Quest::factory()->create([
        'completed_at' => now()->subDays(5),
    ]);
    $recurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
        'recurrence_type' => QuestRecurrence::TYPE_COMPLETION_BASED,
        'last_recurred_at' => now()->subDay(), // Due based on last_recurred_at
    ]);

    // Switch to recurrence-based
    $recurrence->update(['recurrence_type' => QuestRecurrence::TYPE_RECURRENCE_BASED]);

    (new RecurQuests)->handle();

    // Should create a quest based on last_recurred_at (1 day ago + 1 day = due today)
    $this->assertDatabaseCount('quests', 2);
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});

it('uses latest completed child over master completion for completion-based', function () {
    $masterQuest = Quest::factory()->create([
        'completed_at' => now()->subDays(10), // Old completion
    ]);
    $recurrence = QuestRecurrence::factory()->completionBased()->create([
        'quest_id' => $masterQuest->id,
        'every_x_days' => 1,
    ]);

    // Create completed child more recently
    Quest::factory()->create([
        'created_from_recurrence_id' => $recurrence->id,
        'completed_at' => now()->subDay(), // More recent
    ]);

    (new RecurQuests)->handle();

    // Should create based on child completion (1 day ago + 1 day = due today)
    $this->assertDatabaseCount('quests', 3); // Master + old child + new child
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});
