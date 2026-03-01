<?php

declare(strict_types=1);

use App\Services\Nasa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::forget('apod');
});

it('fetches the astronomy picture of the day', function () {
    Http::fake([
        'api.nasa.gov/planetary/apod*' => Http::response([
            'url' => 'https://apod.nasa.gov/apod/image/test.jpg',
            'title' => 'A Nebula',
            'explanation' => 'This is a beautiful nebula.',
            'date' => '2026-02-27',
        ]),
    ]);

    config(['services.nasa.api_key' => 'test-nasa-key']);

    $result = Nasa::apod();

    expect($result)->not->toBeNull()
        ->and($result['url'])->toBe('https://apod.nasa.gov/apod/image/test.jpg')
        ->and($result['title'])->toBe('A Nebula')
        ->and($result['explanation'])->toBe('This is a beautiful nebula.')
        ->and($result['date'])->toBe('2026-02-27');
});

it('merges api response with default keys', function () {
    Http::fake([
        'api.nasa.gov/planetary/apod*' => Http::response([
            'title' => 'Galaxy Far Away',
        ]),
    ]);

    config(['services.nasa.api_key' => 'test-key']);

    $result = Nasa::apod();

    expect($result)->not->toBeNull()
        ->and($result['title'])->toBe('Galaxy Far Away')
        ->and($result['url'])->toBeNull()
        ->and($result['explanation'])->toBeNull();
});

it('returns null when a connection exception occurs', function () {
    config(['services.nasa.api_key' => 'test-key']);

    Http::fake(function () {
        throw new Illuminate\Http\Client\ConnectionException('Connection timed out');
    });

    $result = Nasa::apod();

    expect($result)->toBeNull();
});

it('caches the apod response', function () {
    Http::fake([
        'api.nasa.gov/planetary/apod*' => Http::response([
            'url' => 'https://apod.nasa.gov/apod/image/cached.jpg',
            'title' => 'Cached Image',
            'explanation' => 'This should be cached.',
        ]),
    ]);

    config(['services.nasa.api_key' => 'test-key']);

    $firstResult = Nasa::apod();
    $secondResult = Nasa::apod();

    expect($firstResult['title'])->toBe('Cached Image')
        ->and($secondResult['title'])->toBe('Cached Image');

    Http::assertSentCount(1);
});

it('passes the api key in the request', function () {
    Http::fake([
        'api.nasa.gov/planetary/apod*' => Http::response([
            'url' => 'https://example.com/image.jpg',
            'title' => 'Test',
            'explanation' => 'Test',
        ]),
    ]);

    config(['services.nasa.api_key' => 'my-secret-nasa-key']);

    Nasa::apod();

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api_key=my-secret-nasa-key');
    });
});
