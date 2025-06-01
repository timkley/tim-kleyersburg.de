<?php

declare(strict_types=1);

use App\Models\User;

test('experience', function () {
    $user = User::factory()->create();

    $user->addExperience(-1, 'type', 'identifier-1', 'description');
    expect($user->experience)->toBe(0);

    $user->addExperience(5, 'type', 'identifier-2', 'description');
    expect($user->experience)->toBe(5);

    $user->addExperience(5, 'type', 'identifier-3', 'description');
    expect($user->experience)->toBe(10);

    expect($user->experienceLogs->count())->toBe(3);

    $user->addExperience(5, 'type', 'identifier-3', 'description');
    expect($user->experience)->toBe(10);

    expect($user->experienceLogs->count())->toBe(3);
});
