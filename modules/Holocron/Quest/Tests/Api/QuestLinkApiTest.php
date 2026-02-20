<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Models\Quest;

beforeEach(function () {
    $this->headers = ['Authorization' => 'Bearer '.config('auth.bearer_token')];
});

it('lists links for a quest', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Test']);

    $this->withHeaders($this->headers)
        ->getJson("/api/holocron/quests/{$quest->id}/links")
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('adds a link to a quest', function () {
    Bus::fake();

    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/links", [
            'url' => 'https://example.com',
        ])
        ->assertCreated();

    expect($quest->webpages()->count())->toBe(1);
    Bus::assertDispatched(CrawlWebpageInformation::class);
});

it('validates url on add', function () {
    $quest = Quest::factory()->create();

    $this->withHeaders($this->headers)
        ->postJson("/api/holocron/quests/{$quest->id}/links", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['url']);
});

it('deletes a link', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Test']);

    $pivotId = $quest->webpages()->first()->pivot->id;

    $this->withHeaders($this->headers)
        ->deleteJson("/api/holocron/quests/{$quest->id}/links/{$pivotId}")
        ->assertNoContent();

    expect($quest->webpages()->count())->toBe(0)
        ->and(Webpage::find($webpage->id))->not->toBeNull();
});
