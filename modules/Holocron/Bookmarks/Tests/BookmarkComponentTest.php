<?php

declare(strict_types=1);

use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Bookmark;
use Modules\Holocron\Bookmarks\Models\Webpage;

it('mounts with bookmark data', function () {
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com/some/path',
        'title' => 'Example Title',
        'description' => 'Example description',
        'summary' => 'Example summary',
    ]);
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->assertSet('url', 'https://example.com/some/path')
        ->assertSet('title', 'Example Title')
        ->assertSet('description', 'Example description')
        ->assertSet('summary', 'Example summary')
        ->assertSet('cleanUrl', 'example.com/some/path');
});

it('uses clean url as title when webpage title is null', function () {
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com/page',
        'title' => null,
    ]);
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->assertSet('title', 'example.com/page');
});

it('strips trailing slashes from clean url', function () {
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com/',
    ]);
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->assertSet('cleanUrl', 'example.com');
});

it('dispatches recrawl job', function () {
    Queue::fake([CrawlWebpageInformation::class]);

    $bookmark = Bookmark::factory()->create();

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->call('recrawl');

    Queue::assertPushed(CrawlWebpageInformation::class, function (CrawlWebpageInformation $job) use ($bookmark) {
        return $job->webpage->id === $bookmark->webpage->id;
    });
});

it('updates webpage title via updated hook', function () {
    $webpage = Webpage::factory()->create(['title' => 'Old Title']);
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->set('title', 'New Title');

    expect($webpage->fresh()->title)->toBe('New Title');
});

it('updates webpage description via updated hook', function () {
    $webpage = Webpage::factory()->create(['description' => 'Old description']);
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->set('description', 'New description');

    expect($webpage->fresh()->description)->toBe('New description');
});

it('updates webpage summary via updated hook', function () {
    $webpage = Webpage::factory()->create(['summary' => 'Old summary']);
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->set('summary', 'New summary');

    expect($webpage->fresh()->summary)->toBe('New summary');
});

it('renders the component successfully', function () {
    $bookmark = Bookmark::factory()->create();

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->assertSuccessful();
});

it('handles url without path', function () {
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com',
    ]);
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => $bookmark])
        ->assertSet('cleanUrl', 'example.com');
});
