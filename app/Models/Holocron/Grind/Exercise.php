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
 * @property-read string $name
 * @property-read string $description
 * @property-read string $instructions
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

    public function personalRecord()
    {
        return $this->sets()
            ->select('*', DB::raw('reps * weight as product'))
            ->orderBy('product', 'desc')
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
                'finished_at',
                DB::raw('SUM(reps * weight) as total_volume') // Calculate sum of product
            )
            ->whereNotNull('finished_at')
            ->limit(20)
            ->groupBy('workout_id')
            ->orderBy('finished_at desc')
            ->get()
            ->reverse();
    }
}
