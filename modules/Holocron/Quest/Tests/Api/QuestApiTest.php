<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Models\Quest;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer '.config('auth.bearer_token')];
});

it('requires authentication', function () {
    $this->getJson('/api/holocron/quests')->assertUnauthorized();
});

it('lists quests', function () {
    Quest::factory()->count(3)->create();

    $this->withHeaders($this->headers)
        ->getJson('/api/holocron/quests')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a quest', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/holocron/quests', ['name' => 'New Quest'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'New Quest');
});

it('validates name on create', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/holocron/quests', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

it('shows a quest', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $quest->id);
});

it('shows a quest with relationships via include param', function () {
    $quest = Quest::factory()->create();
    Quest::factory()->create(['quest_id' => $quest->id]);

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}?include=children")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.children');
});

it('updates a quest', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->patchJson("/api/holocron/quests/{$quest->id}", ['name' => 'Updated'])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated');
});

it('deletes a quest', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}")
        ->assertNoContent();

    expect(Quest::find($quest->id))->toBeNull();
});

it('toggles quest complete', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/complete")
        ->assertSuccessful();

    expect($quest->fresh()->completed_at)->not->toBeNull();
});

it('moves a quest', function () {
    $quest = Quest::factory()->create();
    $newParent = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/move", ['quest_id' => $newParent->id])
        ->assertSuccessful()
        ->assertJsonPath('data.quest_id', $newParent->id);
});

it('prints a quest', function () {
    $quest = Quest::factory()->create(['should_be_printed' => false]);

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/print")
        ->assertSuccessful();

    expect((bool) $quest->fresh()->should_be_printed)->toBeTrue();
});

it('toggles quest accept', function () {
    $quest = Quest::factory()->create(['date' => null]);

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/accept")
        ->assertSuccessful();

    expect($quest->fresh()->date)->not->toBeNull();
});

it('returns 404 for nonexistent quest', function () {
    $this->withHeaders($this->headers)
        ->getJson('/api/holocron/quests/99999')
        ->assertNotFound();
});
