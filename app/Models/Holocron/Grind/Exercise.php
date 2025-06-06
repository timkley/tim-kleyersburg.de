<?php

declare(strict_types=1);

namespace App\Models\Holocron\Grind;

use Database\Factories\Holocron\Grind\ExerciseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property string $name
 * @property string $description
 * @property string $instructions
 * @property mixed $pivot
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
     * @return HasMany<Set, $this>
     */
    public function sets(): HasMany
    {
        return $this->hasMany(Set::class);
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
                'workout_id',
                DB::raw('MAX(finished_at) as workout_completed_at'), // Be explicit: e.g., last set's finish time for the workout
                DB::raw('SUM(volume) as total_volume') // Calculate sum of product
            )
            ->whereNotNull('finished_at')
            ->orderBy('workout_completed_at', 'desc') // Order by the aggregated time
            ->groupBy('workout_id', 'finished_at')
            ->limit(30)
            ->get()
            ->reverse();
    }
}
