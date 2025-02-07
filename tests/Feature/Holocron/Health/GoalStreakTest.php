<?php

declare(strict_types=1);

use App\Enums\Holocron\Health\GoalTypes;
use App\Models\Holocron\Health\DailyGoal;

it('calculates the current streak for a goal type', function () {
    $type = GoalTypes::Water;

    // Create valid records for today and the past 4 days (a 5-day streak).
    for ($i = 0; $i < 5; $i++) {
        DailyGoal::create([
            'date' => now()->subDays($i)->toDateString(),
            'type' => $type,
            'amount' => 10,
            'goal' => 10,
            'unit' => 'ml',
        ]);
    }

    // Create a failing record for the day before the streak to break it.
    DailyGoal::create([
        'date' => now()->subDays(5)->toDateString(),
        'type' => $type,
        'amount' => 5, // below goal
        'goal' => 10,
        'unit' => 'ml',
    ]);

    expect(DailyGoal::currentStreakFor($type))->toBe(5);
});

it('calculates the highest streak for a goal type', function () {
    $type = GoalTypes::Water;

    // Create a valid 5-day streak: today (day 0) to 4 days ago.
    for ($i = 0; $i < 5; $i++) {
        DailyGoal::create([
            'date' => now()->subDays($i)->toDateString(),
            'type' => $type,
            'amount' => 10,
            'goal' => 10,
            'unit' => 'ml',
        ]);
    }

    // Break the streak with a failing record on day 5.
    DailyGoal::create([
        'date' => now()->subDays(5)->toDateString(),
        'type' => $type,
        'amount' => 5,  // fails to meet the goal
        'goal' => 10,
        'unit' => 'ml',
    ]);

    // Create another (shorter) streak: day 7 to day 9 (3-day streak).
    // (Note: We skip day 6 entirely to avoid duplicate dates.)
    for ($i = 7; $i < 10; $i++) {
        DailyGoal::create([
            'date' => now()->subDays($i)->toDateString(),
            'type' => $type,
            'amount' => 10,
            'goal' => 10,
            'unit' => 'ml',
        ]);
    }

    // The highest streak should be the 5-day streak.
    expect(DailyGoal::highestStreakFor($type))->toBe(5);
});
