<?php

declare(strict_types=1);

use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

it('resolves tim and identifies tim user', function () {
    $tim = User::factory()->create(['email' => 'timkley@gmail.com']);
    $other = User::factory()->create();

    expect(User::tim()->is($tim))->toBeTrue();
    expect($tim->isTim())->toBeTrue();
    expect($other->isTim())->toBeFalse();
});

it('has a settings relationship', function () {
    $user = User::factory()
        ->has(UserSetting::factory(), 'settings')
        ->create();

    expect($user->settings)->toBeInstanceOf(UserSetting::class);
});

it('returns null when user has no settings', function () {
    $user = User::factory()->create();

    expect($user->settings)->toBeNull();
});

it('hides password and remember_token in serialization', function () {
    $user = User::factory()->create();
    $serialized = $user->toArray();

    expect($serialized)->not->toHaveKey('password');
    expect($serialized)->not->toHaveKey('remember_token');
});

it('casts email_verified_at to datetime', function () {
    $user = User::factory()->create();

    expect($user->email_verified_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('casts email_verified_at to null for unverified users', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});

it('hashes the password via cast', function () {
    $user = User::factory()->create(['password' => 'secret123']);

    expect($user->password)->not->toBe('secret123');
    expect(Hash::check('secret123', $user->password))->toBeTrue();
});
