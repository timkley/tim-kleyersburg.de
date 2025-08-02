<?php

declare(strict_types=1);

namespace App\Models\Holocron\Grind;

use Database\Factories\Holocron\Grind\SetFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $workout_exercise_id
 * @property int $reps
 * @property int $weight
 * @property float $volume
 * @property ?Carbon $started_at
 * @property ?Carbon $finished_at
 * @property ?float $total_volume
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
