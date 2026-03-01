<?php

declare(strict_types=1);

use App\Services\LastFm;
use Illuminate\Support\Facades\Http;

it('fetches recent tracks from the last.fm api', function () {
    Http::fake([
        'ws.audioscrobbler.com/*' => Http::response([
            'recenttracks' => [
                'track' => [
                    [
                        'artist' => ['#text' => 'Radiohead'],
                        'name' => 'Karma Police',
                        'album' => ['#text' => 'OK Computer'],
                        'date' => ['uts' => '1700000000'],
                    ],
                ],
                '@attr' => ['total' => '1234'],
            ],
        ]),
    ]);

    $lastFm = new LastFm(apiKey: 'test-api-key', username: 'test-user');
    $result = $lastFm->getRecentTracks();

    expect($result)
        ->toBeArray()
        ->toHaveKey('track')
        ->toHaveKey('@attr');

    expect($result['track'][0])
        ->toHaveKey('artist')
        ->and(data_get($result, 'track.0.artist.#text'))->toBe('Radiohead')
        ->and(data_get($result, 'track.0.name'))->toBe('Karma Police');
});

it('passes limit and page parameters to the api', function () {
    Http::fake([
        'ws.audioscrobbler.com/*' => Http::response([
            'recenttracks' => [
                'track' => [],
                '@attr' => ['total' => '0', 'page' => '3'],
            ],
        ]),
    ]);

    $lastFm = new LastFm(apiKey: 'my-key', username: 'my-user');
    $lastFm->getRecentTracks(limit: 50, page: 3);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'limit=50')
            && str_contains($request->url(), 'page=3')
            && str_contains($request->url(), 'user=my-user')
            && str_contains($request->url(), 'api_key=my-key')
            && str_contains($request->url(), 'method=user.getrecenttracks');
    });
});

it('returns empty array when api returns null recenttracks', function () {
    Http::fake([
        'ws.audioscrobbler.com/*' => Http::response([]),
    ]);

    $lastFm = new LastFm(apiKey: 'test-key', username: 'test-user');
    $result = $lastFm->getRecentTracks();

    expect($result)->toBe([]);
});

it('fetches top artists from the last.fm api', function () {
    Http::fake([
        'ws.audioscrobbler.com/*' => Http::response([
            'topartists' => [
                'artist' => [
                    ['name' => 'Radiohead', 'url' => 'https://www.last.fm/music/Radiohead', 'playcount' => '150'],
                ],
            ],
        ]),
    ]);

    $lastFm = new LastFm(apiKey: 'test-key', username: 'test-user');
    $result = $lastFm->getTopArtists();

    expect($result)->toBeArray()
        ->toHaveCount(1)
        ->and($result[0]['name'])->toBe('Radiohead')
        ->and($result[0]['url'])->toBe('https://www.last.fm/music/Radiohead');
});

it('passes limit and period parameters to get top artists', function () {
    Http::fake([
        'ws.audioscrobbler.com/*' => Http::response([
            'topartists' => ['artist' => []],
        ]),
    ]);

    $lastFm = new LastFm(apiKey: 'my-key', username: 'my-user');
    $lastFm->getTopArtists(limit: 5, period: '7day');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'limit=5')
            && str_contains($request->url(), 'period=7day')
            && str_contains($request->url(), 'method=user.gettopartists');
    });
});

it('returns empty array when top artists response is missing', function () {
    Http::fake([
        'ws.audioscrobbler.com/*' => Http::response([]),
    ]);

    $lastFm = new LastFm(apiKey: 'test-key', username: 'test-user');
    $result = $lastFm->getTopArtists();

    expect($result)->toBe([]);
});
