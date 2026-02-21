<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Holocron\Grind\Database\Factories\NutritionDayFactory;

/**
 * @property-read \Carbon\CarbonImmutable $date
 * @property-read string $type
 * @property-read ?string $training_label
 * @property-read array<int, array{name: string, time?: string, kcal: int, protein: int, fat: int, carbs: int}> $meals
 * @property-read ?string $notes
 * @property int $total_kcal
 * @property int $total_protein
 * @property int $total_fat
 * @property int $total_carbs
 */
class NutritionDay extends Model
{
    /** @use HasFactory<NutritionDayFactory> */
    use HasFactory;

    protected $table = 'grind_nutrition_days';

    public function recalculateTotals(): void
    {
        $this->total_kcal = (int) collect($this->meals)->sum('kcal');
        $this->total_protein = (int) collect($this->meals)->sum('protein');
        $this->total_fat = (int) collect($this->meals)->sum('fat');
        $this->total_carbs = (int) collect($this->meals)->sum('carbs');
        $this->save();
    }

    protected static function newFactory(): NutritionDayFactory
    {
        return NutritionDayFactory::new();
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'meals' => 'array',
        ];
    }
}
