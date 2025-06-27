<?php

declare(strict_types=1);

use App\Models\Holocron\Quest\Quest;
use App\Models\Holocron\Quest\Reminder;
use App\Notifications\Holocron\QuestReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('it processes due reminders', function () {
    // Mock the Discord notification channel
    Notification::fake();

    // Create a quest
    $quest = Quest::factory()->create(['name' => 'Test Quest']);

    // Create a reminder that's due
    $dueReminder = Reminder::factory()->once()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->subMinutes(5),
    ]);

    // Create a reminder that's not due yet
    $futureReminder = Reminder::factory()->once()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->addHour(),
    ]);

    // Run the command
    $this->artisan('reminders:process')
        ->expectsOutput('Processing 1 due reminders...')
        ->expectsOutput("Processing reminder #{$dueReminder->id} for quest 'Test Quest'")
        ->expectsOutput("Notification sent for reminder #{$dueReminder->id}")
        ->expectsOutput("Reminder #{$dueReminder->id} processed successfully.")
        ->expectsOutput('All due reminders processed successfully.')
        ->assertExitCode(0);

    // Assert that the due reminder is now processed
    expect($dueReminder->fresh()->last_processed_at)->not->toBeNull();

    // Assert that the future reminder is still not processed
    expect($futureReminder->fresh()->last_processed_at)->toBeNull();

    // Assert that a notification was sent for the due reminder
    Notification::assertSentTimes(QuestReminder::class, 1);
});

test('it handles no due reminders', function () {
    // Create a quest
    $quest = Quest::factory()->create(['name' => 'Test Quest']);

    // Create a reminder that's not due yet
    $futureReminder = Reminder::factory()->once()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->addHour(),
    ]);

    // Run the command
    $this->artisan('reminders:process')
        ->expectsOutput('No due reminders found.')
        ->assertExitCode(0);

    // Assert that the future reminder is still not processed
    expect($futureReminder->fresh()->last_processed_at)->toBeNull();
});

test('it updates recurring reminders for next occurrence', function () {
    // Mock the Discord notification channel
    Notification::fake();

    // Create a quest
    $quest = Quest::factory()->create(['name' => 'Test Quest']);

    // Create a recurring reminder with cron pattern that's due
    $cronReminder = Reminder::factory()->cron()->create([
        'quest_id' => $quest->id,
        'remind_at' => now()->subMinutes(5),
    ]);

    // Count reminders before processing
    $beforeCount = Reminder::count();

    // Run the command
    $this->artisan('reminders:process')
        ->expectsOutput('Processing 1 due reminders...')
        ->assertExitCode(0);

    // Count reminders after processing
    $afterCount = Reminder::count();

    // Assert that no new reminder was created (we update the existing one)
    expect($afterCount)->toBe($beforeCount);

    // Refresh the reminder
    $cronReminder->refresh();

    // Assert that the reminder has been updated for the next occurrence
    expect($cronReminder->last_processed_at)->toBeNull()
        ->and($cronReminder->type)->toBe('cron');

    // Check if remind_at is in the future
    expect($cronReminder->remind_at->gt(now()))->toBeTrue();

    // Assert that a notification was sent
    Notification::assertSentTimes(QuestReminder::class, 1);
});
