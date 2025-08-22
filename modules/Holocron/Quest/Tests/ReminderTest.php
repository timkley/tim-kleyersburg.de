<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

uses(RefreshDatabase::class);

test('it can create a reminder', function () {
    $quest = Quest::factory()->create(['name' => 'Test Quest']);

    $reminder = Reminder::factory()->create([
        'quest_id' => $quest->id,
        'type' => 'once',
        'remind_at' => now()->addHour(),
    ]);

    expect($reminder)->not->toBeNull()
        ->and($reminder->quest_id)->toBe($quest->id)
        ->and($reminder->type)->toBe('once');
});

test('it can find due reminders', function () {
    // Create a quest
    $quest = Quest::factory()->create(['name' => 'Test Quest']);

    // Create a reminder that's due
    $dueReminder = Reminder::factory()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->subMinutes(5),
    ]);

    // Create a reminder that's not due yet
    $futureReminder = Reminder::factory()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->addHour(),
    ]);

    // Create a reminder that's already processed
    $processedReminder = Reminder::factory()->processed()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->subHour(),
    ]);

    // Get due reminders
    $dueReminders = Reminder::due()->get();

    // Assert that only the due reminder is returned
    expect($dueReminders)->toHaveCount(1)
        ->and($dueReminders->first()->id)->toBe($dueReminder->id);
});

test('it can mark reminder as processed', function () {
    // Create a quest
    $quest = Quest::factory()->create(['name' => 'Test Quest']);

    // Create a one-time reminder
    $reminder = Reminder::factory()->once()->create([
        'quest_id' => $quest->id,
        'remind_at' => now(),
    ]);

    // Mark as processed
    $reminder->markAsProcessed();

    // Assert that the reminder is marked as processed
    expect($reminder->last_processed_at)->not->toBeNull();
});

test('it updates the current reminder for recurring reminders', function () {
    // Create a quest
    $quest = Quest::factory()->create(['name' => 'Test Quest']);

    // Create a recurring reminder with cron pattern
    $reminder = Reminder::factory()->cron()->create([
        'quest_id' => $quest->id,
        'remind_at' => now(),
    ]);

    // Count reminders before processing
    $beforeCount = Reminder::count();

    // Mark as processed (this should update the current reminder for the next occurrence)
    $reminder->markAsProcessed();

    // Count reminders after processing
    $afterCount = Reminder::count();

    // Assert that no new reminder was created
    expect($afterCount)->toBe($beforeCount);

    // Refresh the reminder
    $reminder->refresh();

    // Assert that the reminder has been updated for the next occurrence
    expect($reminder->last_processed_at)->toBeNull()
        ->and($reminder->type)->toBe('cron');

    // Check if remind_at is in the future
    expect($reminder->remind_at->gt(now()))->toBeTrue();
});

test('it uses cron expression for calculating next occurrence', function () {
    // Create a quest
    $quest = Quest::factory()->create(['name' => 'Test Quest']);

    // Create a reminder with a specific cron expression for Mondays
    $reminder = Reminder::factory()->create([
        'quest_id' => $quest->id,
        'type' => 'cron',
        'remind_at' => now(),
        'recurrence_pattern' => '0 9 * * 1', // Every Monday at 9 AM
    ]);

    // Mark as processed
    $reminder->markAsProcessed();

    // Refresh the reminder
    $reminder->refresh();

    // Get the next Monday
    $nextMonday = Carbon::now()->next(Carbon::MONDAY)->setHour(9)->setMinute(0)->setSecond(0);

    // Assert that the reminder is scheduled for the next Monday at 9 AM
    expect($reminder->remind_at->format('Y-m-d H:i'))
        ->toBe($nextMonday->format('Y-m-d H:i'));
});
