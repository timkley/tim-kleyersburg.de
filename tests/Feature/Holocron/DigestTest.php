<?php

declare(strict_types=1);

use function Pest\Laravel\post;
use function Pest\Laravel\withToken;

it('can store a digest with a correct bearer token', function () {
    Config::set('auth.bearer_token', 'test');

    withToken('test')
        ->post(route('store-digest'), ['body' => 'My digest value'])
        ->assertJson(['message' => 'Digest stored']);
});

it('correctly authorizes the request', function () {
    post(route('store-digest'), ['body' => 'test'])
        ->assertUnauthorized();

    expect(cache('daily-digest'))->toBeNull();
});
