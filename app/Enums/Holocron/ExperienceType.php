<?php

declare(strict_types=1);

namespace App\Enums\Holocron;

enum ExperienceType: string
{
    case WorkoutFinished = 'workout_finished';
    case QuestCompleted = 'quest_completed';
    case PerfectDay = 'perfect_day';
    case GoalReached = 'goal_reached';
    case GoalUnreached = 'goal_unreached';
    case StreakGoalReached = 'streak_goal_reached';

    public function label(): string
    {
        return match ($this) {
            self::WorkoutFinished => 'Training abgeschlossen',
            self::QuestCompleted => 'Quest abgeschlossen',
            self::PerfectDay => 'Perfekter Tag',
            self::GoalReached => 'Ziel erreicht',
            self::GoalUnreached => 'Ziel verloren',
            self::StreakGoalReached => 'Streak-Ziel erreicht',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::WorkoutFinished => 'Du hast wieder fett gepumpt, mein Kerl.',
            self::QuestCompleted => 'Die NPCs danken dir.',
            self::PerfectDay => 'An Tagen wie diesen...',
            self::GoalReached => 'Nice.',
            self::GoalUnreached => 'Kein Durchhaltevermögen, der Typ.',
            self::StreakGoalReached => 'Deine Streak wächst und wächst!',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WorkoutFinished => 'biceps-flexed',
            self::QuestCompleted => 'circle-dot',
            self::PerfectDay => 'calendar',
            self::GoalReached => 'hand-thumb-up',
            self::GoalUnreached => 'hand-thumb-down',
            self::StreakGoalReached => 'fire',
        };
    }
}
