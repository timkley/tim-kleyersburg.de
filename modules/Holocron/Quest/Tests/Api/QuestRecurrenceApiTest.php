<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer '.config('auth.bearer_token')];
});

it('shows recurrence for a quest', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/recurrence")
        ->assertSuccessful()
        ->assertJsonPath('data.quest_id', $quest->id);
});

it('returns null when no recurrence exists', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/recurrence")
        ->assertSuccessful()
        ->assertJsonPath('data', null);
});

it('creates a recurrence', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/recurrence", [
            'every_x_days' => 7,
            'recurrence_type' => 'recurrence_based',
        ])
        ->assertCreated()
        ->assertJsonPath('data.every_x_days', 7);
});

it('validates required fields', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/recurrence", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['every_x_days', 'recurrence_type']);
});

it('deletes a recurrence', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/recurrence")
        ->assertNoContent();

    expect($quest->recurrence()->count())->toBe(0);
});
