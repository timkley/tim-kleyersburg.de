<?php

declare(strict_types=1);

use App\Livewire\Pages\Components\LastScrobble;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    Cache::forget('lastfm:last-scrobble');
    Cache::forget('lastfm:weekly-artist');
});

it('renders the last scrobble and top artist', function () {
    Http::fake(function ($request) {
        if (str_contains($request->url(), 'method=user.getrecenttracks')) {
            return Http::response([
                'recenttracks' => [
                    'track' => [
                        [
                            'artist' => ['#text' => 'Radiohead'],
                            'name' => 'Karma Police',
                            'album' => ['#text' => 'OK Computer'],
                            'image' => [
                                ['#text' => 'small.jpg'],
                                ['#text' => 'medium.jpg'],
                                ['#text' => 'large.jpg'],
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return Http::response([
            'topartists' => [
                'artist' => [
                    ['name' => 'Radiohead', 'url' => 'https://last.fm/music/Radiohead', 'playcount' => '150'],
                ],
            ],
        ]);
    });

    Livewire::withoutLazyLoading()
        ->test(LastScrobble::class)
        ->assertSee('Karma Police')
        ->assertSee('Radiohead')
        ->assertSee('150')
        ->assertOk();
});

it('renders the placeholder when no track data is available', function () {
    $component = new LastScrobble;

    $placeholder = $component->placeholder();

    expect($placeholder->getName())->toBe('pages.components.last-scrobble');
});

it('caches the last scrobble result', function () {
    Http::fake(function ($request) {
        if (str_contains($request->url(), 'method=user.getrecenttracks')) {
            return Http::response([
                'recenttracks' => [
                    'track' => [
                        [
                            'artist' => ['#text' => 'Radiohead'],
                            'name' => 'Creep',
                            'album' => ['#text' => 'Pablo Honey'],
                            'image' => [
                                ['#text' => 'small.jpg'],
                                ['#text' => 'medium.jpg'],
                                ['#text' => 'large.jpg'],
                            ],
                        ],
                    ],
                ],
            ]);
        }

        return Http::response([
            'topartists' => [
                'artist' => [
                    ['name' => 'Radiohead', 'url' => 'https://last.fm/music/Radiohead', 'playcount' => '200'],
                ],
            ],
        ]);
    });

    // Render the component twice - the Http calls should only happen once due to caching
    Livewire::withoutLazyLoading()->test(LastScrobble::class)->assertSee('Creep');
    Livewire::withoutLazyLoading()->test(LastScrobble::class)->assertSee('Creep');

    Http::assertSentCount(2);
});

it('is a lazy-loaded livewire component', function () {
    $reflection = new ReflectionClass(LastScrobble::class);
    $attributes = $reflection->getAttributes(\Livewire\Attributes\Lazy::class);

    expect($attributes)->toHaveCount(1);
});
