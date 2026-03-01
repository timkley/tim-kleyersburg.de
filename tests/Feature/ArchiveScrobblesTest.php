<?php

declare(strict_types=1);

use App\Jobs\ArchiveScrobbles;
use App\Models\Scrobble;
use App\Services\LastFm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('does nothing when local scrobble count matches remote total', function () {
    foreach (range(1, 5) as $i) {
        Scrobble::query()->create([
            'artist' => "Artist {$i}",
            'track' => "Track {$i}",
            'album' => "Album {$i}",
            'played_at' => Carbon::now()->subMinutes($i),
            'payload' => json_encode([]),
        ]);
    }

    $requestCount = 0;

    Http::fake(function ($request) use (&$requestCount) {
        $requestCount++;

        return Http::response([
            'recenttracks' => [
                '@attr' => ['total' => '5'],
                'track' => [],
            ],
        ]);
    });

    $job = new ArchiveScrobbles;
    $job->handle(app(LastFm::class));

    expect(Scrobble::query()->count())->toBe(5)
        ->and($requestCount)->toBe(1);
});

it('fetches and stores new scrobbles when counts differ', function () {
    $callCount = 0;

    Http::fake(function ($request) use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'recenttracks' => [
                    '@attr' => ['total' => '3'],
                    'track' => [],
                ],
            ]);
        }

        return Http::response([
            'recenttracks' => [
                'track' => [
                    [
                        'artist' => ['#text' => 'Radiohead'],
                        'album' => ['#text' => 'OK Computer'],
                        'name' => 'Karma Police',
                        'date' => ['uts' => '1700000000'],
                    ],
                    [
                        'artist' => ['#text' => 'Radiohead'],
                        'album' => ['#text' => 'OK Computer'],
                        'name' => 'Paranoid Android',
                        'date' => ['uts' => '1700000300'],
                    ],
                    [
                        'artist' => ['#text' => 'Radiohead'],
                        'album' => ['#text' => 'The Bends'],
                        'name' => 'Fake Plastic Trees',
                        'date' => ['uts' => '1700000600'],
                    ],
                ],
                '@attr' => ['total' => '3'],
            ],
        ]);
    });

    Queue::fake();

    $job = new ArchiveScrobbles;
    $job->handle(app(LastFm::class));

    expect(Scrobble::query()->count())->toBe(3);

    $scrobble = Scrobble::query()->where('track', 'Karma Police')->first();
    expect($scrobble)->not->toBeNull()
        ->and($scrobble->artist)->toBe('Radiohead')
        ->and($scrobble->album)->toBe('OK Computer');
});

it('skips currently playing tracks that have no date', function () {
    $callCount = 0;

    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'recenttracks' => [
                    '@attr' => ['total' => '2'],
                    'track' => [],
                ],
            ]);
        }

        return Http::response([
            'recenttracks' => [
                'track' => [
                    [
                        'artist' => ['#text' => 'Radiohead'],
                        'album' => ['#text' => 'OK Computer'],
                        'name' => 'Lucky',
                        '@attr' => ['nowplaying' => 'true'],
                    ],
                    [
                        'artist' => ['#text' => 'Radiohead'],
                        'album' => ['#text' => 'OK Computer'],
                        'name' => 'Karma Police',
                        'date' => ['uts' => '1700000000'],
                    ],
                ],
                '@attr' => ['total' => '2'],
            ],
        ]);
    });

    Queue::fake();

    $job = new ArchiveScrobbles;
    $job->handle(app(LastFm::class));

    expect(Scrobble::query()->count())->toBe(1)
        ->and(Scrobble::query()->first()->track)->toBe('Karma Police');
});

it('dispatches another job for the previous page when not on page 1', function () {
    $callCount = 0;

    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'recenttracks' => [
                    '@attr' => ['total' => '1500'],
                    'track' => [],
                ],
            ]);
        }

        return Http::response([
            'recenttracks' => [
                'track' => [
                    [
                        'artist' => ['#text' => 'Artist'],
                        'album' => ['#text' => 'Album'],
                        'name' => 'Track',
                        'date' => ['uts' => '1700000000'],
                    ],
                ],
                '@attr' => ['total' => '1500'],
            ],
        ]);
    });

    Queue::fake();

    $job = new ArchiveScrobbles(page: 3);
    $job->handle(app(LastFm::class));

    Queue::assertPushed(ArchiveScrobbles::class);
});

it('does not dispatch another job when on page 1', function () {
    $callCount = 0;

    Http::fake(function () use (&$callCount) {
        $callCount++;

        if ($callCount === 1) {
            return Http::response([
                'recenttracks' => [
                    '@attr' => ['total' => '100'],
                    'track' => [],
                ],
            ]);
        }

        return Http::response([
            'recenttracks' => [
                'track' => [
                    [
                        'artist' => ['#text' => 'Artist'],
                        'album' => ['#text' => 'Album'],
                        'name' => 'Track',
                        'date' => ['uts' => '1700000000'],
                    ],
                ],
                '@attr' => ['total' => '100'],
            ],
        ]);
    });

    Queue::fake();

    $job = new ArchiveScrobbles(page: 1);
    $job->handle(app(LastFm::class));

    Queue::assertNotPushed(ArchiveScrobbles::class);
});

it('implements ShouldQueue and ShouldBeUniqueUntilProcessing', function () {
    $job = new ArchiveScrobbles;

    expect($job)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class)
        ->and($job)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing::class);
});
