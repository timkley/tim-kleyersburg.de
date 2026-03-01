<?php

declare(strict_types=1);

use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $setting = UserSetting::factory()->create(['user_id' => $user->id]);

    expect($setting->user->is($user))->toBeTrue();
});

it('casts nutrition_daily_targets to array', function () {
    $targets = ['rest' => ['kcal' => 2000, 'protein' => 140]];
    $user = User::factory()->create();
    $setting = UserSetting::factory()->create([
        'user_id' => $user->id,
        'nutrition_daily_targets' => $targets,
    ]);

    $setting->refresh();

    expect($setting->nutrition_daily_targets)->toBe($targets);
});

it('allows null nutrition_daily_targets', function () {
    $user = User::factory()->create();
    $setting = UserSetting::factory()->create([
        'user_id' => $user->id,
        'nutrition_daily_targets' => null,
    ]);

    expect($setting->nutrition_daily_targets)->toBeNull();
});

it('stores weight as a float', function () {
    $user = User::factory()->create();
    $setting = UserSetting::factory()->create([
        'user_id' => $user->id,
        'weight' => 82.5,
    ]);

    $setting->refresh();

    expect($setting->weight)->toBe(82.5);
});
