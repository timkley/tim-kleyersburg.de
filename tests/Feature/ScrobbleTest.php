<?php

declare(strict_types=1);

use App\Models\Scrobble;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

it('has the correct casts', function () {
    $scrobble = new Scrobble;

    $casts = (new ReflectionMethod($scrobble, 'casts'))->invoke($scrobble);

    expect($casts)
        ->toHaveKey('played_at', 'datetime')
        ->toHaveKey('payload', 'array');
});

it('does not use timestamps', function () {
    $scrobble = new Scrobble;

    expect($scrobble->timestamps)->toBeFalse();
});

it('can be created with the expected attributes', function () {
    $scrobble = Scrobble::query()->create([
        'artist' => 'Radiohead',
        'track' => 'Karma Police',
        'album' => 'OK Computer',
        'played_at' => Carbon::parse('2026-01-15 14:30:00'),
        'payload' => json_encode(['key' => 'value']),
    ]);

    expect($scrobble)->toBeInstanceOf(Scrobble::class)
        ->and($scrobble->artist)->toBe('Radiohead')
        ->and($scrobble->track)->toBe('Karma Police')
        ->and($scrobble->album)->toBe('OK Computer')
        ->and($scrobble->played_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($scrobble->played_at->format('Y-m-d H:i:s'))->toBe('2026-01-15 14:30:00');
});

it('enforces the unique constraint on artist, track, and played_at', function () {
    $attributes = [
        'artist' => 'Radiohead',
        'track' => 'Karma Police',
        'album' => 'OK Computer',
        'played_at' => Carbon::parse('2026-01-15 14:30:00'),
        'payload' => json_encode([]),
    ];

    Scrobble::query()->create($attributes);

    // Upserting the same record should not create a duplicate
    Scrobble::query()->upsert([$attributes], ['artist', 'track', 'played_at']);

    expect(Scrobble::query()->count())->toBe(1);
});

it('allows the same track by same artist at different times', function () {
    Scrobble::query()->create([
        'artist' => 'Radiohead',
        'track' => 'Karma Police',
        'album' => 'OK Computer',
        'played_at' => Carbon::parse('2026-01-15 14:30:00'),
        'payload' => json_encode([]),
    ]);

    Scrobble::query()->create([
        'artist' => 'Radiohead',
        'track' => 'Karma Police',
        'album' => 'OK Computer',
        'played_at' => Carbon::parse('2026-01-15 15:00:00'),
        'payload' => json_encode([]),
    ]);

    expect(Scrobble::query()->count())->toBe(2);
});
