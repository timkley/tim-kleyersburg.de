<?php

declare(strict_types=1);

use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('shows the login page', function () {
    $response = get(route('holocron.login'));

    expect($response->status())->toBe(200);
});

it('can auto login locally when enabled by configuration', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);

    config()->set('auth.local_auto_login.enabled', true);
    config()->set('auth.local_auto_login.email', $user->email);

    get(route('holocron.login'))
        ->assertRedirect(route('holocron.dashboard'));

    expect(auth()->id())->toBe($user->id);
});

it('is possible to login', function () {
    $user = User::factory()->create();

    $response = Livewire\Livewire::test('holocron.user.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertSessionDoesntHaveErrors();

    expect($response)->isRedirect(route('holocron.dashboard'));
    expect(auth()->check())->toBeTrue();
});

it('is not possible to login without correct credentials', function () {
    $user = User::factory()->create();

    Livewire\Livewire::test('holocron.user.login')
        ->set('email', $user->email)
        ->set('password', 'lol')
        ->call('login')
        ->assertHasErrors();

    expect(auth()->check())->toBeFalse();
});

it('does not expose the experience page', function () {
    $user = User::factory()->create();
    actingAs($user);

    get('/holocron/experience')->assertNotFound();
});
