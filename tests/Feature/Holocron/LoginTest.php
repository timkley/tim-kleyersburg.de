<?php

use App\Models\User;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('shows the login page', function () {
    $response = get(route('holocron.login'));

    expect($response)->isSuccessful();
    expect($response)->assertViewIs('holocron.login');
});

it('is possible to login', function () {
    $user = User::factory()->create();

    $response = post(route('holocron.login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect($response)->isRedirect(route('holocron.dashboard'));
    expect(auth()->check())->toBeTrue();
});

it('is not possible to login without correct credentials', function () {
    $user = User::factory()->create();

    $response = post(route('holocron.login'), [
        'email' => $user->email,
        'password' => 'lol',
    ]);

    expect($response)->assertSessionHasErrors();
    expect(auth()->check())->toBeFalse();
});
