<?php

declare(strict_types=1);

namespace App\Models\Holocron\Grind;

use Database\Factories\Holocron\Grind\WorkoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read ?Carbon $started_at
 * @property-read ?Carbon $finished_at
 */
class Workout extends Model
{
    /** @use HasFactory<WorkoutFactory> */
    use HasFactory;

    /** * @var string */
    protected $table = 'grind_workouts';

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * @return HasMany<Set, $this>
     */
    public function sets(): HasMany
    {
        return $this->hasMany(Set::class);
    }

    /**
     * Get the current exercise to work on
     */
    public function getCurrentExercise(): ?Exercise
    {
        return $this->plan->exercises()
            ->orderBy('grind_exercise_plan.order')
            ->skip($this->current_exercise_index)
            ->first();
    }

    /**
     * Advance to the next exercise
     */
    public function advanceToNextExercise(): void
    {
        $this->increment('current_exercise_index');
    }

    /**
     * @return string[]
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
        ];
    }
}
