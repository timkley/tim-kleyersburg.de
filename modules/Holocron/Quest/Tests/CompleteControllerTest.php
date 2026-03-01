<?php

declare(strict_types=1);

use Illuminate\Support\Facades\URL;
use Modules\Holocron\Quest\Models\Quest;

it('completes a quest via signed URL', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    $url = URL::signedRoute('holocron.quests.complete', ['id' => $quest->id]);

    $this->get($url)
        ->assertSuccessful()
        ->assertSee($quest->name)
        ->assertSee('erfolgreich abgeschlossen');

    expect($quest->fresh()->completed_at)->not->toBeNull();
});

it('rejects unsigned request', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    $this->get(route('holocron.quests.complete', ['id' => $quest->id]))
        ->assertForbidden();

    expect($quest->fresh()->completed_at)->toBeNull();
});

it('rejects request with invalid signature', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    $url = URL::signedRoute('holocron.quests.complete', ['id' => $quest->id]);

    $this->get($url.'tampered')
        ->assertForbidden();
});
