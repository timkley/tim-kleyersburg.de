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
