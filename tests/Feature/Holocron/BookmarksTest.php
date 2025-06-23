<?php

declare(strict_types=1);

use App\Jobs\CrawlWebpageInformation;
use App\Models\Holocron\Bookmark;
use App\Models\Webpage;

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

it('can delete a bookmark', function () {
    $bookmark = Bookmark::factory()->create();

    Livewire::test('holocron.bookmarks.index')
        ->call('delete', $bookmark->id);

    expect(Bookmark::where('id', $bookmark->id)->exists())->toBeFalse();
});
