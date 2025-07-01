<?php

declare(strict_types=1);

namespace App\Services\Holocron\Health;

use App\Enums\Holocron\ExperienceType;
use App\Enums\Holocron\Health\GoalType;
use App\Models\Holocron\Health\DailyGoal;
use App\Models\User;

class StreakGoalService
{
    /**
     * The streak goals that can be reached.
     * These are the streak lengths at which a streak goal is awarded.
     *
     * @var array<int>
     */
    protected static array $streakGoals = [5, 10, 15, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 125, 150, 175, 200];

    /**
     * The base XP awarded for reaching a goal.
     */
    protected static int $baseXp = 2;

    /**
     * Check if a streak goal has been reached for the given goal type.
     *
     * @param  GoalType  $goalType  The type of goal
     */
    public static function checkStreakGoals(GoalType $goalType): void
    {
        $currentStreak = DailyGoal::currentStreakFor($goalType);

        // If the current streak matches one of the streak goals, award XP
        if (in_array($currentStreak, self::$streakGoals)) {
            self::awardStreakGoalXp($goalType, $currentStreak);
        }

        // Also award scaled XP for the daily goal based on the current streak
        self::awardScaledDailyXp($goalType, $currentStreak);
    }

    /**
     * Calculate the scaled XP to award for the daily goal based on the current streak.
     *
     * @param  int  $streak  The current streak
     * @return int The scaled XP to award
     */
    public static function calculateScaledDailyXp(int $streak): int
    {
        // Start with the base XP
        $scaledXp = self::$baseXp;

        // Increase XP by 0.1 for each day in the streak
        $scaledXp += $streak * 0.1;

        // Round to the nearest integer
        return (int) round($scaledXp);
    }

    /**
     * Create a unique identifier for a streak goal.
     *
     * @param  GoalType  $goalType  The type of goal
     * @param  int  $streak  The streak length
     * @return int A unique identifier
     */
    public static function createIdentifier(GoalType $goalType, int $streak): int
    {
        // Create a unique identifier by combining the goal type and streak
        // This ensures that a streak goal is only awarded once per streak and goal
        return crc32($goalType->value.'_streak_'.$streak);
    }

    /**
     * Award XP for reaching a streak goal.
     *
     * @param  GoalType  $goalType  The type of goal
     * @param  int  $streak  The current streak
     */
    protected static function awardStreakGoalXp(GoalType $goalType, int $streak): void
    {
        // Calculate XP based on the streak length
        // The longer the streak, the more XP is awarded
        $xp = self::calculateStreakGoalXp($streak);

        // Create a unique identifier for this streak goal
        // This ensures that a streak goal is only awarded once per streak and goal
        $identifier = self::createIdentifier($goalType, $streak);

        // Award the XP
        User::tim()->addExperience($xp, ExperienceType::StreakGoalReached, $identifier);
    }

    /**
     * Award scaled XP for the daily goal based on the current streak.
     *
     * @param  GoalType  $goalType  The type of goal
     * @param  int  $streak  The current streak
     */
    protected static function awardScaledDailyXp(GoalType $goalType, int $streak): void
    {
        // Get the latest goal for this type
        $goal = DailyGoal::for($goalType);

        // If the goal is not reached, don't award XP
        if (! $goal->reached) {
            return;
        }

        // Calculate the scaled XP based on the streak
        $scaledXp = self::calculateScaledDailyXp($streak);

        // If the scaled XP is the same as the base XP, don't do anything special
        if ($scaledXp <= self::$baseXp) {
            return;
        }

        // Create a unique identifier for this daily goal's streak bonus
        // We use crc32 to convert the string to an integer
        $identifier = crc32($goal->id.'_streak_bonus');

        // Award the additional XP (on top of the base XP that's already awarded)
        User::tim()->addExperience($scaledXp - self::$baseXp, ExperienceType::GoalReached, $identifier);
    }

    /**
     * Calculate the XP to award for reaching a streak goal.
     *
     * @param  int  $streak  The current streak
     * @return int The XP to award
     */
    protected static function calculateStreakGoalXp(int $streak): int
    {
        // The longer the streak, the more XP is awarded
        // This formula increases XP more aggressively with the streak length
        return (int) (5 + ($streak / 2));
    }
}
