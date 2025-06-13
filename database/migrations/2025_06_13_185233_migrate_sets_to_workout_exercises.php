<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grind_sets', function (Blueprint $table) {
            $table->unsignedInteger('workout_exercise_id')->nullable()->after('exercise_id');
        });

        $allSets = DB::table('grind_sets')->get();
        $allSets = $allSets->groupBy(['workout_id', 'exercise_id']);

        foreach ($allSets as $workoutId => $workout) {
            $order = 1;
            foreach ($workout as $exerciseId => $exercise) {
                $sets = $exercise->count();
                $minReps = $exercise->min('reps');
                $maxReps = $exercise->max('reps');
                $order = $order + 1;

                DB::table('grind_workout_exercises')->where('workout_id', $workoutId)->where('exercise_id', $exerciseId)->updateOrInsert([
                    'workout_id' => $workoutId,
                    'exercise_id' => $exerciseId,
                ], [
                    'sets' => $sets,
                    'min_reps' => $minReps,
                    'max_reps' => $maxReps,
                    'order' => $order,
                ]);

                $workoutExerciseId = DB::table('grind_workout_exercises')
                    ->where('workout_id', $workoutId)
                    ->where('exercise_id', $exerciseId)
                    ->value('id');

                foreach ($exercise as $set) {
                    DB::table('grind_sets')->where('id', $set->id)->update([
                        'workout_exercise_id' => $workoutExerciseId,
                    ]);
                }
            }
        }

        Schema::table('grind_sets', function (Blueprint $table) {
            $table->removeColumn('exercise_id');
            $table->removeColumn('workout_id');
        });
    }
};
