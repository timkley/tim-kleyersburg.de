<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\Reminder;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer '.config('auth.bearer_token')];
});

it('lists reminders for a quest', function () {
    $quest = Quest::factory()->create();
    Reminder::factory()->count(2)->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/reminders")
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('creates a reminder', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/reminders", [
            'remind_at' => '2026-03-01 09:00',
            'type' => 'once',
        ])
        ->assertCreated();

    expect($quest->reminders()->count())->toBe(1);
});

it('validates required fields', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/reminders", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['remind_at', 'type']);
});

it('deletes a reminder', function () {
    $quest = Quest::factory()->create();
    $reminder = Reminder::factory()->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/reminders/{$reminder->id}")
        ->assertNoContent();

    expect(Reminder::find($reminder->id))->toBeNull();
});
