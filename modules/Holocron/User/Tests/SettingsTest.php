<?php

declare(strict_types=1);

use Modules\Holocron\User\Livewire\Settings;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;

it('shows the settings page', function () {
    $user = User::factory()
        ->has(UserSetting::factory(), 'settings')
        ->create(['email' => 'timkley@gmail.com']);

    actingAs($user)
        ->get(route('holocron.settings'))
        ->assertSuccessful()
        ->assertSeeLivewire(Settings::class);
});

it('loads current weight on mount', function () {
    $user = User::factory()
        ->has(UserSetting::factory()->state(['weight' => 82.5]), 'settings')
        ->create(['email' => 'timkley@gmail.com']);

    actingAs($user);

    Livewire\Livewire::test(Settings::class)
        ->assertSet('weight', 82.5);
});

it('updates weight in the database when changed', function () {
    $user = User::factory()
        ->has(UserSetting::factory()->state(['weight' => 80.0]), 'settings')
        ->create(['email' => 'timkley@gmail.com']);

    actingAs($user);

    Livewire\Livewire::test(Settings::class)
        ->set('weight', 85.0);

    expect($user->settings->fresh()->weight)->toBe(85.0);
});
