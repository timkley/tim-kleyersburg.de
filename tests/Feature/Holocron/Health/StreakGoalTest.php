<?php

declare(strict_types=1);

use App\Enums\Holocron\ExperienceType;
use App\Enums\Holocron\Health\GoalType;
use App\Models\Holocron\Health\DailyGoal;
use App\Models\User;
use App\Services\Holocron\Health\StreakGoalService;

use function Pest\Laravel\actingAs;

it('awards experience for reaching a streak goal', function () {
    $user = User::factory(['email' => 'timkley@gmail.com'])->create();
    actingAs($user);

    $type = GoalType::Water;

    // Create a 5-day streak (which is a streak goal)
    for ($i = 0; $i < 5; $i++) {
        DailyGoal::create([
            'date' => now()->subDays($i)->toDateString(),
            'type' => $type,
            'amount' => 10,
            'goal' => 10,
            'unit' => 'ml',
        ]);
    }

    // Verify the streak is 5
    expect(DailyGoal::currentStreakFor($type))->toBe(5);

    // Create a new goal for today and track it to trigger the streak goal check
    $goal = DailyGoal::for($type);
    $goal->track(10);

    // Check that experience was awarded for the streak goal
    $experienceLogs = $user->fresh()->experienceLogs;
    expect($experienceLogs->where('type', ExperienceType::StreakGoalReached)->count())->toBe(1);
});

it('awards experience only once per streak goal', function () {
    $user = User::factory(['email' => 'timkley@gmail.com'])->create();
    actingAs($user);

    $type = GoalType::Water;

    // Create a 5-day streak (which is a streak goal)
    for ($i = 0; $i < 5; $i++) {
        DailyGoal::create([
            'date' => now()->subDays($i)->toDateString(),
            'type' => $type,
            'amount' => 10,
            'goal' => 10,
            'unit' => 'ml',
        ]);
    }

    // Create a new goal for today and track it to trigger the streak goal check
    $goal = DailyGoal::for($type);
    $goal->track(10);

    // Track it again to make sure the streak goal is not awarded twice
    $goal->track(10);

    // Check that experience was awarded only once for the streak goal
    $experienceLogs = $user->fresh()->experienceLogs;
    expect($experienceLogs->where('type', ExperienceType::StreakGoalReached)->count())->toBe(1);
});

it('awards scaled experience for daily goals based on streak length', function () {
    $user = User::factory(['email' => 'timkley@gmail.com'])->create();
    actingAs($user);

    $type = GoalType::Water;

    // Create a 10-day streak
    for ($i = 0; $i < 10; $i++) {
        DailyGoal::create([
            'date' => now()->subDays($i)->toDateString(),
            'type' => $type,
            'amount' => 10,
            'goal' => 10,
            'unit' => 'ml',
        ]);
    }

    // Verify the streak is 10
    expect(DailyGoal::currentStreakFor($type))->toBe(10);

    // Create a new goal for today and track it to trigger the streak goal check
    $goal = DailyGoal::for($type);
    $goal->track(10);

    // Check that experience was awarded for the daily goal
    $experienceLogs = $user->fresh()->experienceLogs;

    // We should have at least 2 experience logs:
    // 1. The base 2 XP for reaching the goal
    // 2. Additional XP for the streak (scaled based on streak length)
    expect($experienceLogs->where('type', ExperienceType::GoalReached)->count())->toBeGreaterThanOrEqual(2);

    // Calculate the expected scaled XP
    $scaledXp = StreakGoalService::calculateScaledDailyXp(10);

    // The total XP awarded should be at least the scaled XP
    $totalXp = $experienceLogs->where('type', ExperienceType::GoalReached)->sum('amount');
    expect($totalXp)->toBeGreaterThanOrEqual($scaledXp);
});

it('awards more XP for bigger streaks', function () {
    $user = User::factory(['email' => 'timkley@gmail.com'])->create();
    actingAs($user);

    // Use different goal types for the 5-day and 10-day streaks
    $type5Day = GoalType::Water;
    $type10Day = GoalType::Creatine;

    // Create a 5-day streak
    for ($i = 0; $i < 5; $i++) {
        DailyGoal::create([
            'date' => now()->subDays($i)->toDateString(),
            'type' => $type5Day,
            'amount' => 10,
            'goal' => 10,
            'unit' => 'ml',
        ]);
    }

    // Create a new goal for today and track it to trigger the streak goal check
    $goal5Day = DailyGoal::for($type5Day);
    $goal5Day->track(10);

    // Get the XP awarded for the 5-day streak
    $xpFor5DayStreak = $user->fresh()->experienceLogs->where('type', ExperienceType::StreakGoalReached)->sum('amount');

    // Create a 10-day streak for a different goal type
    for ($i = 0; $i < 10; $i++) {
        DailyGoal::create([
            'date' => now()->subDays($i)->toDateString(),
            'type' => $type10Day,
            'amount' => 10,
            'goal' => 10,
            'unit' => 'g',
        ]);
    }

    // Create a new goal for today and track it to trigger the streak goal check
    $goal10Day = DailyGoal::for($type10Day);
    $goal10Day->track(10);

    // Get the XP awarded for the 10-day streak
    $xpFor10DayStreak = $user->fresh()->experienceLogs->where('type', ExperienceType::StreakGoalReached)->where('identifier', '!=', StreakGoalService::createIdentifier($type5Day, 5))->sum('amount');

    // The XP for the 10-day streak should be greater than the XP for the 5-day streak
    expect($xpFor10DayStreak)->toBeGreaterThan($xpFor5DayStreak);
});
