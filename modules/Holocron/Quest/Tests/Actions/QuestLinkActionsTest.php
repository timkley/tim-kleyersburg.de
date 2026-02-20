<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Actions\AddQuestLink;
use Modules\Holocron\Quest\Actions\DeleteQuestLink;
use Modules\Holocron\Quest\Models\Quest;

it('adds a link to a quest and dispatches a crawl job', function () {
    Bus::fake();

    $quest = Quest::factory()->create();

    $result = (new AddQuestLink)->handle($quest, ['url' => 'https://example.com']);

    expect($result->webpages)->toHaveCount(1)
        ->and($result->webpages->first()->url)->toBe('https://example.com');

    Bus::assertDispatched(CrawlWebpageInformation::class);
});

it('attaches the quest to the webpage and only dispatches the crawl job once per new webpage', function () {
    Bus::fake();

    $quest = Quest::factory()->create();

    // First call: new webpage created, job dispatched
    (new AddQuestLink)->handle($quest, ['url' => 'https://example.com']);

    Bus::assertDispatched(CrawlWebpageInformation::class, 1);

    expect($quest->webpages()->count())->toBe(1)
        ->and(Webpage::where('url', 'https://example.com')->count())->toBeGreaterThanOrEqual(1);
});

it('validates that url is required', function () {
    $quest = Quest::factory()->create();

    (new AddQuestLink)->handle($quest, []);
})->throws(Illuminate\Validation\ValidationException::class);

it('validates that url has a valid format', function () {
    $quest = Quest::factory()->create();

    (new AddQuestLink)->handle($quest, ['url' => 'not-a-url']);
})->throws(Illuminate\Validation\ValidationException::class);

it('deletes a quest link via pivot without deleting the webpage', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create(['url' => 'https://example.com']);

    $quest->webpages()->attach($webpage, ['title' => null]);
    $pivotId = $quest->webpages()->first()->pivot->id;

    (new DeleteQuestLink)->handle($quest, $pivotId);

    expect($quest->webpages()->count())->toBe(0)
        ->and(Webpage::find($webpage->id))->not->toBeNull();
});
