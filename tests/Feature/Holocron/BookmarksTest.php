<?php

declare(strict_types=1);

use App\Jobs\CrawlWebpageInformation;
use App\Models\Holocron\Bookmark;
use App\Models\Webpage;
use Denk\Facades\Denk;
use OpenAI\Responses\Chat\CreateResponse;

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.bookmarks'))->assertRedirect(route('holocron.login'));
});

it('can add a bookmark', function () {
    Queue::fake([CrawlWebpageInformation::class]);

    Livewire::test('holocron.bookmarks.overview')
        ->set('url', 'https://example.com')
        ->call('submit');

    expect(Webpage::where('url', 'https://example.com')->exists())->toBeTrue();
    expect(Bookmark::where('webpage_id', 1)->exists())->toBeTrue();
});

it('validates', function () {
    Queue::fake([CrawlWebpageInformation::class]);

    Livewire::test('holocron.bookmarks.overview')
        ->set('url', 'asdf')
        ->call('submit')
        ->assertHasErrors();

    Livewire::test('holocron.bookmarks.overview')
        ->set('url', '')
        ->call('submit')
        ->assertHasErrors();

    Livewire::test('holocron.bookmarks.overview')
        ->set('url', 'https://example.com')
        ->call('submit')
        ->assertHasNoErrors();
});

it('can delete a bookmark', function () {
    $bookmark = Bookmark::factory()->create();

    Livewire::test('holocron.bookmarks.overview')
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
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com',
    ]);
    $bookmark = Bookmark::factory()->create([
        'webpage_id' => $webpage->id,
    ]);
    (new CrawlWebpageInformation($webpage))->handle();

    expect($webpage->title)->toBe('Example title');
    expect($webpage->description)->toBe('Example description');
    expect($webpage->summary)->toBe('Good day sir!!');
});

it('can recrawl information', function () {
    Queue::fake();
    Livewire::test('holocron.bookmarks.components.bookmark', ['bookmark' => Bookmark::factory()->create()])
        ->call('recrawl');

    Queue::assertPushed(CrawlWebpageInformation::class);
});
