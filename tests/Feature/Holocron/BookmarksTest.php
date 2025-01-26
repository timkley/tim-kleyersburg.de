<?php

declare(strict_types=1);

use App\Jobs\Holocron\CrawlBookmarkInformation;
use App\Models\Holocron\Bookmark;
use Denk\Facades\Denk;
use OpenAI\Responses\Chat\CreateResponse;

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
get(route('holocron.bookmarks'))
->assertRedirect(route('holocron.login'));
    });

it('can add a bookmark', function () {
    Queue::fake([CrawlBookmarkInformation::class]);

    Livewire::test('holocron.bookmarks.bookmarks')
        ->set('url', 'https://example.com')
        ->call('submit');

    expect(Bookmark::where('url', 'https://example.com')->exists())->toBeTrue();
});

it('validates', function () {
    Queue::fake([CrawlBookmarkInformation::class]);

    Livewire::test('holocron.bookmarks.bookmarks')
        ->set('url', 'asdf')
        ->call('submit')
        ->assertHasErrors();

    Livewire::test('holocron.bookmarks.bookmarks')
        ->set('url', '')
        ->call('submit')
        ->assertHasErrors();

    Livewire::test('holocron.bookmarks.bookmarks')
        ->set('url', 'https://example.com')
        ->call('submit')
        ->assertHasNoErrors();

    Livewire::test('holocron.bookmarks.bookmarks')
        ->set('url', 'https://example.com')
        ->call('submit')
        ->assertHasErrors();
});

it('can delete a bookmark', function () {
    $bookmark = Bookmark::factory()->create();

    Livewire::test('holocron.bookmarks.bookmarks')
        ->call('delete', $bookmark->id);

    expect(Bookmark::where('id', $bookmark->id)->exists())->toBeFalse();
});

it('dispatches a job that crawls for more content', function () {
    Http::fake([
        'https://firecrawl.wacg.dev/*' => Http::response(file_get_contents(base_path('tests/fixtures/example.json'))),
    ]);
    Denk::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Good day sir!!',
                    ],
                ],
            ],
        ]),
    ]);
    $bookmark = Bookmark::factory()->create([
        'url' => 'https://example.com',
        'title' => null,
        'description' => null,
        'summary' => null,
    ]);
    (new CrawlBookmarkInformation($bookmark))->handle();

    expect($bookmark->title)->toBe('Example title');
    expect($bookmark->description)->toBe('Example description');
    expect($bookmark->summary)->toBe('Good day sir!!');
});

it('can recrawl information', function () {
    Queue::fake();
    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => Bookmark::factory()->create()])
        ->call('recrawl');

    Queue::assertPushed(CrawlBookmarkInformation::class);
});
