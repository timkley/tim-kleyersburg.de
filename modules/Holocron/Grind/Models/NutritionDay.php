<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Holocron\Grind\Database\Factories\NutritionDayFactory;
use Modules\Holocron\Grind\Observers\MealObserver;

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

        app(MealObserver::class)->syncProteinGoal($day);
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
}
