<?php

declare(strict_types=1);

use Modules\Holocron\User\Enums\ExperienceType;
use Modules\Holocron\User\Models\User;

test('experience', function () {
    $user = User::factory(['email' => 'timkley@gmail.com'])->create();

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
