<?php

declare(strict_types=1);

namespace App\Concerns\Holocron\Health;

use App\Enums\Holocron\Health\GoalType;
use App\Models\Holocron\Health\DailyGoal;
use App\Models\User;
use App\Services\Weather;
use Carbon\CarbonImmutable;

trait CalculatesGoals
{
    public function goal(): int
    {
        return match ($this) {
            self::Water => $this->waterGoal(),
            self::Creatine => 5,
            self::Planks => $this->plankGoal(),
            self::Mobility => 1,
            self::NoSmoking => 1,
            self::NoAlcohol => 1,
            self::Protein => $this->proteinGoal(),
        };
    }

    public function defaultAmount(): int
    {
        return match ($this) {
            self::NoSmoking => 1,
            self::NoAlcohol => 1,
            default => 0,
        };
    }

    protected function waterGoal(): int
    {
        $user = User::tim();
        $weight = $user->settings?->weight;
        $temperature = Weather::forecast('Fellbach', CarbonImmutable::now(), CarbonImmutable::now())->avgMaxTemp;
        $goal = $weight * 0.033;

        match (true) {
            $temperature >= 30 => $goal += 0.75,
            $temperature >= 25 => $goal += 0.5,
            default => null,
        };

        return (int) ($goal * 1000);
    }

    protected function plankGoal(): int
    {
        return max(90, (int) DailyGoal::where('type', GoalType::Planks)->max('amount') + 5);
    }

    protected function proteinGoal(): int
    {
        $user = User::tim();
        $weight = $user->settings?->weight;

        return (int) round($weight * 1.2);
    }
}
