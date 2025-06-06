<?php

declare(strict_types=1);

use App\Enums\Holocron\ExperienceType;
use App\Models\User;

test('experience', function () {
    $user = User::factory()->create();

    $user->addExperience(-1, ExperienceType::QuestCompleted, 1);
    expect($user->experience)->toBe(0);

    $user->addExperience(5, ExperienceType::QuestCompleted, 2);
    expect($user->experience)->toBe(5);

    $user->addExperience(5, ExperienceType::QuestCompleted, 3);
    expect($user->experience)->toBe(10);

    expect($user->experienceLogs->count())->toBe(3);

    $user->addExperience(5, ExperienceType::QuestCompleted, 3);
    expect($user->experience)->toBe(10);

    expect($user->experienceLogs->count())->toBe(3);
});
