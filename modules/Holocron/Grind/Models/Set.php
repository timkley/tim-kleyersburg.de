<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Holocron\Grind\Database\Factories\SetFactory;

/**
 * @property int $workout_exercise_id
 * @property int $reps
 * @property int $weight
 * @property float $volume
 * @property ?Carbon $started_at
 * @property ?Carbon $finished_at
 * @property ?float $total_volume
 * @property ?string $workout_completed_at
 */
class Set extends Model
{
    /** @use HasFactory<SetFactory> */
    use HasFactory;

    protected $table = 'grind_sets';

    /**
     * @return BelongsTo<WorkoutExercise, $this>
     */
    public function workoutExercise(): BelongsTo
    {
        return $this->belongsTo(WorkoutExercise::class);
    }

    protected static function newFactory(): SetFactory
    {
        return SetFactory::new();
    }

    /**
     * @param  EloquentBuilder<Set>  $query
     * @return EloquentBuilder<Set>
     */
    #[Scope]
    protected function siblings(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('workout_exercise_id', $this->workout_exercise_id);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
