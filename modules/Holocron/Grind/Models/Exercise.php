<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use Modules\Holocron\Grind\Database\Factories\ExerciseFactory;

/**
 * @property-read string $name
 * @property-read string $description
 * @property-read string $instructions
 * @property-read mixed $pivot
 */
class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'grind_exercises';

    /**
     * @return BelongsToMany<Plan, $this>
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'grind_exercise_plan');
    }

    /**
     * @return HasManyThrough<Set, WorkoutExercise, $this>
     */
    public function sets(): HasManyThrough
    {
        return $this->hasManyThrough(Set::class, WorkoutExercise::class);
    }

    public function personalRecord(): ?Set
    {
        return $this->sets()
            ->limit(1)
            ->orderBy('volume', 'desc')
            ->first();
    }

    /**
     * @return Collection<int, Set>
     */
    public function volumePerWorkout(): Collection
    {
        return $this->sets()
            ->select(
                'grind_workout_exercises.workout_id',
                DB::raw('COALESCE(MAX(grind_workouts.finished_at), MAX(grind_workouts.started_at)) as workout_finished_at'),
                DB::raw('SUM(volume) as total_volume')
            )
            ->join('grind_workouts', 'grind_workout_exercises.workout_id', '=', 'grind_workouts.id')
            ->whereNotNull('grind_sets.finished_at')
            ->orderBy('workout_finished_at', 'desc')
            ->groupBy('grind_workout_exercises.workout_id')
            ->limit(30)
            ->get()
            ->reverse();
    }

    protected static function newFactory(): ExerciseFactory
    {
        return ExerciseFactory::new();
    }
}
