<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Holocron\Grind\Database\Factories\WorkoutExerciseFactory;

/**
 * @property-read int $exercise_id
 * @property int $sets
 */
class WorkoutExercise extends Model
{
    /** @use HasFactory<WorkoutExerciseFactory> */
    use HasFactory;

    /** * @var string */
    protected $table = 'grind_workout_exercises';

    /**
     * @return BelongsTo<Exercise, $this>
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * @return BelongsTo<Workout, $this>
     */
    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    /**
     * @return HasMany<Set, $this>
     */
    public function sets(): HasMany
    {
        return $this->hasMany(Set::class);
    }

    protected static function newFactory(): WorkoutExerciseFactory
    {
        return WorkoutExerciseFactory::new();
    }
}
