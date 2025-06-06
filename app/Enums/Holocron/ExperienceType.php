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

    public function label(): string
    {
        return match ($this) {
            self::WorkoutFinished => 'Training abgeschlossen',
            self::QuestCompleted => 'Quest abgeschlossen',
            self::PerfectDay => 'Perfekter Tag',
            self::GoalReached => 'Ziel erreicht',
            self::GoalUnreached => 'Ziel verloren',
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
        };
    }
}
