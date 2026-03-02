<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Holocron\Grind\Database\Factories\MealFactory;

/**
 * @property-read int $id
 * @property-read int $nutrition_day_id
 * @property-read string $name
 * @property-read ?string $time
 * @property-read int $kcal
 * @property-read int $protein
 * @property-read int $fat
 * @property-read int $carbs
 */
class Meal extends Model
{
    /** @use HasFactory<MealFactory> */
    use HasFactory;

    protected $table = 'grind_meals';

    /**
     * @return BelongsTo<NutritionDay, $this>
     */
    public function nutritionDay(): BelongsTo
    {
        return $this->belongsTo(NutritionDay::class);
    }

    protected static function newFactory(): MealFactory
    {
        return MealFactory::new();
    }
}
