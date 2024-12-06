<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\get;

it('shows the login page', function () {
    $response = get(route('holocron.login'));

    expect($response->status())->toBe(200);
});

it('is possible to login', function () {
    $user = User::factory()->create();

    $response = Livewire\Livewire::test('holocron.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertSessionDoesntHaveErrors();

    expect($response)->isRedirect(route('holocron.dashboard'));
    expect(auth()->check())->toBeTrue();
});

it('is not possible to login without correct credentials', function () {
    $user = User::factory()->create();

    Livewire\Livewire::test('holocron.login')
        ->set('email', $user->email)
        ->set('password', 'lol')
        ->call('login')
        ->assertHasErrors();

    expect(auth()->check())->toBeFalse();
});
