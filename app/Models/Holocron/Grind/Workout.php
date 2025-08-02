<?php

declare(strict_types=1);

namespace App\Models\Holocron\Grind;

use Database\Factories\Holocron\Grind\WorkoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property ?Carbon $started_at
 * @property ?Carbon $finished_at
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
     * @return HasMany<WorkoutExercise, $this>
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(WorkoutExercise::class)->orderBy('order');
    }

    /**
     * @return HasManyThrough<Set, WorkoutExercise, $this>
     */
    public function sets(): HasManyThrough
    {
        return $this->hasManyThrough(Set::class, WorkoutExercise::class);
    }

    /**
     * Get the current exercise to work on
     */
    public function getCurrentExercise(): ?WorkoutExercise
    {
        return $this->exercises()->find($this->current_exercise_id) ?? $this->exercises()->first();
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
            'finished_at' => 'datetime',
        ];
    }
}
