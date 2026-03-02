<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Holocron\Grind\Database\Factories\NutritionDayFactory;
use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

/**
 * @property-read \Carbon\CarbonImmutable $date
 * @property-read string $type
 * @property-read ?string $training_label
 * @property-read ?string $notes
 */
class NutritionDay extends Model
{
    /** @use HasFactory<NutritionDayFactory> */
    use HasFactory;

    protected $table = 'grind_nutrition_days';

    public static function markAsDayType(string $type, ?string $trainingLabel = null, ?CarbonInterface $date = null): void
    {
        $day = static::query()->firstOrCreate(
            ['date' => $date ?? today()],
            ['type' => 'rest'],
        );

        $updateData = ['type' => $type];

        if ($trainingLabel !== null) {
            $updateData['training_label'] = $trainingLabel;
        }

        $day->update($updateData);

        $day->syncProteinGoalProjection();
    }

    /**
     * @return HasMany<Meal, $this>
     */
    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }

    protected static function newFactory(): NutritionDayFactory
    {
        return NutritionDayFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    private function syncProteinGoalProjection(): void
    {
        $user = $this->resolveGoalUser();

        if ($user === null) {
            return;
        }

        $goal = DailyGoal::query()->firstOrNew(
            [
                'date' => $this->date->toDateString(),
                'type' => GoalType::Protein->value,
            ],
            [
                'unit' => GoalType::Protein->unit()->value,
            ],
        );

        $goal->fill([
            'unit' => GoalType::Protein->unit()->value,
            'goal' => $this->proteinTargetFor($user),
            'amount' => (int) $this->meals()->sum('protein'),
        ]);

        $goal->save();
    }

    private function proteinTargetFor(User $user): int
    {
        $target = $user->settings?->nutrition_daily_targets[$this->type]['protein'] ?? null;

        if (is_numeric($target)) {
            return (int) $target;
        }

        $weight = $user->settings?->weight;

        if ($weight === null) {
            return 0;
        }

        return (int) round($weight * 2);
    }

    private function resolveGoalUser(): ?User
    {
        $authenticatedUser = auth()->user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        return User::query()
            ->where('email', 'timkley@gmail.com')
            ->first();
    }
}
