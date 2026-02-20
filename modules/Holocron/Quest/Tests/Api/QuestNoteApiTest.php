<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer '.config('auth.bearer_token')];
});

it('lists notes for a quest', function () {
    $quest = Quest::factory()->create();
    Note::factory()->for($quest)->count(3)->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/notes")
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a note', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/notes", [
            'content' => 'Test note',
        ])
        ->assertCreated()
        ->assertJsonPath('data.content', 'Test note');
});

it('validates content on create', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/notes", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

it('deletes a note', function () {
    $quest = Quest::factory()->create();
    $note = Note::factory()->for($quest)->create();

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/notes/{$note->id}")
        ->assertNoContent();

    expect(Note::find($note->id))->toBeNull();
});
