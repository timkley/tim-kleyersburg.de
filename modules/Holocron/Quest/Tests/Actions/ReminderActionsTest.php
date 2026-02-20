<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\DeleteReminder;
use Modules\Holocron\Quest\Actions\SaveReminder;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

it('creates a one-time reminder', function () {
    $quest = Quest::factory()->create();

    $reminder = (new SaveReminder)->handle($quest, [
        'remind_at' => '2025-06-01 09:00:00',
        'type' => 'once',
    ]);

    expect($reminder)->toBeInstanceOf(Reminder::class)
        ->and($reminder->quest_id)->toBe($quest->id)
        ->and((string) $reminder->type)->toBe('once');
});

it('updates an existing reminder when id is provided', function () {
    $quest = Quest::factory()->create();
    $existing = Reminder::factory()->for($quest)->create([
        'remind_at' => '2025-06-01 09:00:00',
        'type' => 'once',
    ]);

    $updated = (new SaveReminder)->handle($quest, [
        'id' => $existing->id,
        'remind_at' => '2025-07-01 10:00:00',
        'type' => 'once',
    ]);

    expect($updated->id)->toBe($existing->id)
        ->and($updated->remind_at->format('Y-m-d'))->toBe('2025-07-01');
});

it('validates that remind_at is required', function () {
    $quest = Quest::factory()->create();

    (new SaveReminder)->handle($quest, ['type' => 'once']);
})->throws(Illuminate\Validation\ValidationException::class);

it('validates that type is required', function () {
    $quest = Quest::factory()->create();

    (new SaveReminder)->handle($quest, ['remind_at' => '2025-06-01 09:00:00']);
})->throws(Illuminate\Validation\ValidationException::class);

it('deletes a reminder', function () {
    $reminder = Reminder::factory()->create();

    (new DeleteReminder)->handle($reminder);

    expect(Reminder::find($reminder->id))->toBeNull();
});
