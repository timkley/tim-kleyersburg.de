<?php

declare(strict_types=1);

use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Bookmark;
use Modules\Holocron\Bookmarks\Models\Webpage;

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.bookmarks'))->assertRedirect(route('holocron.login'));
});

it('can add a bookmark', function () {
    Queue::fake([CrawlWebpageInformation::class]);

    Livewire::test('holocron.bookmarks.index')
        ->set('url', 'https://example.com')
        ->call('submit');

    expect(Webpage::where('url', 'https://example.com')->exists())->toBeTrue();
    expect(Bookmark::where('webpage_id', 1)->exists())->toBeTrue();
});

it('dispatches crawl job when adding a bookmark', function () {
    Queue::fake([CrawlWebpageInformation::class]);

    Livewire::test('holocron.bookmarks.index')
        ->set('url', 'https://example.com')
        ->call('submit');

    Queue::assertPushed(CrawlWebpageInformation::class, function (CrawlWebpageInformation $job) {
        return $job->webpage->url === 'https://example.com';
    });
});

it('resets url field after submission', function () {
    Queue::fake([CrawlWebpageInformation::class]);

    Livewire::test('holocron.bookmarks.index')
        ->set('url', 'https://example.com')
        ->call('submit')
        ->assertSet('url', '');
});

it('validates', function () {
    Queue::fake([CrawlWebpageInformation::class]);

    Livewire::test('holocron.bookmarks.index')
        ->set('url', 'asdf')
        ->call('submit')
        ->assertHasErrors();

    Livewire::test('holocron.bookmarks.index')
        ->set('url', '')
        ->call('submit')
        ->assertHasErrors();

    Livewire::test('holocron.bookmarks.index')
        ->set('url', 'https://example.com')
        ->call('submit')
        ->assertHasNoErrors();
});

it('validates url is required', function () {
    Livewire::test('holocron.bookmarks.index')
        ->set('url', '')
        ->call('submit')
        ->assertHasErrors('url');
});

it('validates url must be a valid url', function () {
    Livewire::test('holocron.bookmarks.index')
        ->set('url', 'not-a-url')
        ->call('submit')
        ->assertHasErrors('url');
});

it('can delete a bookmark', function () {
    $bookmark = Bookmark::factory()->create();

    Livewire::test('holocron.bookmarks.index')
        ->call('delete', $bookmark->id);

    expect(Bookmark::where('id', $bookmark->id)->exists())->toBeFalse();
});

it('renders bookmarks list', function () {
    $bookmark = Bookmark::factory()->create();

    Livewire::test('holocron.bookmarks.index')
        ->assertSuccessful()
        ->assertSee($bookmark->webpage->title);
});

it('paginates bookmarks', function () {
    Bookmark::factory()->count(25)->create();

    $component = Livewire::test('holocron.bookmarks.index');

    expect(Bookmark::count())->toBe(25);
    $component->assertSuccessful();
});

it('has a belongs to webpage relationship', function () {
    $webpage = Webpage::factory()->create();
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    expect($bookmark->webpage)->toBeInstanceOf(Webpage::class);
    expect($bookmark->webpage->id)->toBe($webpage->id);
});

it('can create a bookmark using factory', function () {
    $bookmark = Bookmark::factory()->create();

    expect($bookmark)->toBeInstanceOf(Bookmark::class);
    expect($bookmark->webpage)->toBeInstanceOf(Webpage::class);
    expect($bookmark->exists)->toBeTrue();
});

it('generates a searchable array', function () {
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com',
        'title' => 'Example',
        'description' => 'A description',
        'summary' => 'A summary',
    ]);
    $bookmark = Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    $searchable = $bookmark->toSearchableArray();

    expect($searchable)->toHaveKeys(['id', 'url', 'title', 'description', 'summary', 'created_at']);
    expect($searchable['id'])->toBe((string) $bookmark->id);
    expect($searchable['url'])->toBe('https://example.com');
    expect($searchable['title'])->toBe('Example');
    expect($searchable['description'])->toBe('A description');
    expect($searchable['summary'])->toBe('A summary');
    expect($searchable['created_at'])->toBe($bookmark->created_at->timestamp);
});

it('does not delete the associated webpage when deleting a bookmark', function () {
    $bookmark = Bookmark::factory()->create();
    $webpageId = $bookmark->webpage_id;

    $bookmark->delete();

    expect(Webpage::where('id', $webpageId)->exists())->toBeTrue();
});

it('searches bookmarks when query is set', function () {
    config(['scout.driver' => 'collection']);

    $webpage = Webpage::factory()->create(['title' => 'Laravel Documentation']);
    Bookmark::factory()->create(['webpage_id' => $webpage->id]);

    Livewire::test('holocron.bookmarks.index')
        ->set('query', 'Laravel')
        ->assertSuccessful();
});
