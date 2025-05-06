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
 * @property-read int $exercise_id
 * @property-read int $workout_id
 * @property-read int $reps
 * @property-read int $weight
 * @property-read ?Carbon $started_at
 * @property-read ?Carbon $finished_at
 */
class Set extends Model
{
    /** @use HasFactory<SetFactory> */
    use HasFactory;

    protected $table = 'grind_sets';

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

    #[Scope]
    protected function siblings(EloquentBuilder $query): EloquentBuilder
    {
        return $query->where('workout_id', $this->workout_id);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
