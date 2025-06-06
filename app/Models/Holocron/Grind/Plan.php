<?php

declare(strict_types=1);

namespace App\Models\Holocron\Grind;

use Database\Factories\Holocron\Grind\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $name
 * @property string $description
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'grind_plans';

    /** The exercises belonging to the plan.
     * @return BelongsToMany<Exercise, $this>
     */
    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'grind_exercise_plan')->withPivot('sets', 'min_reps', 'max_reps', 'order');
    }
}
