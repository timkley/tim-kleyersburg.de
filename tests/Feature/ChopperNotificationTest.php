<?php

declare(strict_types=1);

use App\Notifications\Chopper;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\AnonymousAgent;

beforeEach(function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [
                ['latitude' => 48.8, 'longitude' => 9.27],
            ],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => [now()->format('Y-m-d')],
                'weather_code' => [0],
                'temperature_2m_max' => [15.0],
                'temperature_2m_min' => [5.0],
                'precipitation_sum' => [0.0],
            ],
        ]),
    ]);
});

it('returns the agent response text from a conversation', function () {
    AnonymousAgent::fake(['Hallo Tim!']);

    $result = Chopper::conversation('Hallo Chopper!', 'test-topic');

    expect($result)->toBe('Hallo Tim!');
});

it('stores conversation history in cache', function () {
    AnonymousAgent::fake(['Erste Antwort']);

    Chopper::conversation('Erste Nachricht', 'cache-topic');

    $history = cache('chopper.cache-topic');

    expect($history)->toHaveCount(2)
        ->and($history[0])->toBeInstanceOf(Laravel\Ai\Messages\UserMessage::class)
        ->and($history[1])->toBeInstanceOf(Laravel\Ai\Messages\AssistantMessage::class);
});

it('appends to existing conversation history', function () {
    AnonymousAgent::fake(['Antwort 1', 'Antwort 2']);

    Chopper::conversation('Nachricht 1', 'multi-topic');
    Chopper::conversation('Nachricht 2', 'multi-topic');

    $history = cache('chopper.multi-topic');

    expect($history)->toHaveCount(4);
});

it('uses a custom ttl when provided', function () {
    AnonymousAgent::fake(['OK']);

    $ttl = CarbonImmutable::now()->addMinutes(5);

    Chopper::conversation('Test', 'ttl-topic', $ttl);

    expect(cache('chopper.ttl-topic'))->not->toBeNull();
});

it('uses end of day as default ttl', function () {
    AnonymousAgent::fake(['OK']);

    Chopper::conversation('Test', 'default-ttl-topic');

    expect(cache('chopper.default-ttl-topic'))->not->toBeNull();
});

it('logs conversation to the chopper channel', function () {
    AnonymousAgent::fake(['Logged response']);

    Log::shouldReceive('channel')
        ->with('chopper')
        ->once()
        ->andReturnSelf();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(function (string $message, array $context) {
            return $message === 'Chopper'
                && $context['topic'] === 'log-topic'
                && is_array($context['history']);
        });

    Chopper::conversation('Log test', 'log-topic');
});
