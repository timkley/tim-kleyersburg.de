<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Modules\Holocron\Quest\Enums\ReminderType;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

it('belongs to a quest', function () {
    $quest = Quest::factory()->create();
    $reminder = Reminder::factory()->for($quest)->create();

    expect($reminder->quest)->toBeInstanceOf(Quest::class)
        ->and($reminder->quest->id)->toBe($quest->id);
});

it('uses quest_reminders table', function () {
    $reminder = new Reminder;

    expect($reminder->getTable())->toBe('quest_reminders');
});

it('casts remind_at as datetime', function () {
    $reminder = Reminder::factory()->create(['remind_at' => '2025-06-01 09:00:00']);

    expect($reminder->remind_at)->toBeInstanceOf(CarbonImmutable::class);
});

it('casts last_processed_at as datetime', function () {
    $reminder = Reminder::factory()->processed()->create();

    expect($reminder->last_processed_at)->toBeInstanceOf(CarbonImmutable::class);
});

it('scopes due reminders correctly', function () {
    $quest = Quest::factory()->create();

    $due = Reminder::factory()->for($quest)->create([
        'remind_at' => now()->subMinutes(5),
        'last_processed_at' => null,
    ]);

    $future = Reminder::factory()->for($quest)->create([
        'remind_at' => now()->addHour(),
        'last_processed_at' => null,
    ]);

    $processed = Reminder::factory()->for($quest)->create([
        'remind_at' => now()->subHour(),
        'last_processed_at' => now(),
    ]);

    $dueReminders = Reminder::due()->get();

    expect($dueReminders)->toHaveCount(1)
        ->and($dueReminders->first()->id)->toBe($due->id);
});

it('includes reminders where last_processed_at is before remind_at in due scope', function () {
    $quest = Quest::factory()->create();

    $reminder = Reminder::factory()->for($quest)->create([
        'remind_at' => now()->subMinutes(5),
        'last_processed_at' => now()->subDay(),
    ]);

    expect(Reminder::due()->count())->toBe(1);
});

it('marks a one-time reminder as processed', function () {
    $reminder = Reminder::factory()->once()->create([
        'remind_at' => now()->subMinute(),
    ]);

    $reminder->markAsProcessed();

    expect($reminder->fresh()->last_processed_at)->not->toBeNull();
});

it('schedules next occurrence for cron reminder when marked processed', function () {
    $this->travelTo(CarbonImmutable::parse('2025-08-24 10:00:00'));

    $reminder = Reminder::factory()->cron()->create([
        'remind_at' => now(),
    ]);

    $reminder->markAsProcessed();
    $reminder->refresh();

    expect($reminder->remind_at->gt(now()))->toBeTrue()
        ->and($reminder->last_processed_at)->toBeNull();
});

it('creates a one-time reminder via factory state', function () {
    $reminder = Reminder::factory()->once()->create();

    expect($reminder->type)->toBe(ReminderType::Once)
        ->and($reminder->recurrence_pattern)->toBeNull();
});

it('creates a cron reminder via factory state', function () {
    $reminder = Reminder::factory()->cron()->create();

    expect($reminder->type)->toBe(ReminderType::Cron)
        ->and($reminder->recurrence_pattern)->toBe('0 9 * * *');
});

it('throws exception when scheduling next occurrence without recurrence pattern', function () {
    $reminder = Reminder::factory()->once()->create([
        'remind_at' => now()->subMinute(),
    ]);

    // Use reflection to call the protected method directly
    $method = new ReflectionMethod($reminder, 'scheduleNextOccurrence');

    expect(fn () => $method->invoke($reminder))
        ->toThrow(InvalidArgumentException::class, 'Cannot calculate next occurrence without a recurrence pattern');
});

it('creates a processed reminder via factory state', function () {
    $reminder = Reminder::factory()->processed()->create();

    expect($reminder->last_processed_at)->not->toBeNull();
});
