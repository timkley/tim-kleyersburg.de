<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Holocron\Grind\Exercise;
use App\Models\Holocron\Grind\Plan;
use App\Models\Holocron\Grind\Set;
use App\Models\Holocron\Grind\Workout;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWorkouts extends Command
{
    /** @var string */
    protected $signature = 'import:ap-workouts {file}';

    /** @var string */
    protected $description = 'Import workout data from a file into Grind module';

    // Mapping for plan names
    protected array $planMapping = [
        'GK 1' => 1,
        'GK2' => 2,
    ];

    // Mapping for exercise names
    protected array $exerciseMapping = [
        'Kniebeugen' => 8,
        'Beinbeugen am Beinstrecker' => 9,
        'Klimmzüge eng mit Neutralgriff' => 10,
        'Bankdrücken' => 11,
        'Rudern mit Brustauflage eng' => 12,
        'Butterfly eng' => 13,
        'Trizepsdrücken' => 14,
        'Preacher Curls' => 7,
        'Curls' => 7,
        'Kreuzheben' => 1,
        'Schulterdrücken im Stehen' => 2,
        'Beinpresse' => 3,
        'Latzug weit mit Obergriff' => 4,
        'Brustpresse' => 5,
        'Butterfly Reverse weit' => 6,
        'Hammer Curls mit dem Seil' => 7,
        'Fliegende' => 13,
    ];

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! file_exists($file)) {
            $this->error("File not found: $file");

            return 1;
        }

        $data = file_get_contents($file);
        $workouts = preg_split('/(?="[\w\s]+·\s*Tag\s*\d+\s*·\s*Woche\s*\d+)/i', $data, -1, PREG_SPLIT_NO_EMPTY);

        $this->info('Found '.count($workouts).' workouts to process');

        foreach ($workouts as $workoutData) {
            DB::beginTransaction();
            try {
                $this->processWorkout($workoutData);
                DB::commit();
                $this->info('Workout committed to database');
            } catch (Exception $e) {
                DB::rollBack();
                $this->error('Error processing workout: '.$e->getTraceAsString());
                // Continue with next workout
            }
        }

        $this->info('Import completed');

        return 0;
    }

    protected function processWorkout($workoutData): void
    {
        $this->info('Processing workout data');
        $lines = explode("\n", (string) $workoutData);

        // Process header information
        $headerLine = mb_trim($lines[0], "\"\r");

        preg_match('/^(.+)·\s*Tag\s*(\d+)\s*·\s*Woche\s*(\d+)/i', $headerLine, $matches);
        if (! isset($matches[1])) {
            throw new Exception("Invalid workout header format: $headerLine");
        }

        $planName = mb_trim($matches[1]);
        $day = isset($matches[2]) ? mb_trim($matches[2]) : 'unknown';
        $week = isset($matches[3]) ? mb_trim($matches[3]) : 'unknown';

        // Parse date and duration
        preg_match('/(\d{4}-\d{2}-\d{2})\s+(\d{1,2}:\d{2})/i', $headerLine, $dateMatches);
        $startedAt = Carbon::parse($dateMatches[1].' '.$dateMatches[2]);

        [$hours, $minutes] = $this->parseDuration($headerLine);
        $finishedAt = (clone $startedAt)->addHours($hours)->addMinutes($minutes);

        if ($finishedAt->diffInMinutes($startedAt, true) < 10) {
            return;
        }

        $this->info("Processing workout: $planName, Day $day, Week $week, Date: {$startedAt->format('Y-m-d H:i')}");

        // Map plan name to actual plan
        $mappedPlanId = $this->planMapping[$planName] ?? null;

        if (! $mappedPlanId) {
            return;
        }

        $plan = Plan::find($mappedPlanId);

        if (! $plan) {
            throw new Exception("Plan not found: $mappedPlanId");
        }

        // Create workout
        $workout = Workout::create([
            'plan_id' => $plan->id,
            'current_exercise_index' => 0,
            'started_at' => $startedAt,
            'finished_at' => $finishedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("Created workout ID: {$workout->id}");

        // Process exercises and sets
        $currentExercise = null;
        $inSetsSection = false;
        $counter = count($lines);

        for ($i = 1; $i < $counter; $i++) {
            $line = mb_trim($lines[$i], "\"\r");
            // Skip empty lines
            if ($line === '') {
                continue;
            }
            if ($line === '0') {
                continue;
            }

            // Check if this is an exercise header
            if (preg_match('/^(\d+)\.\s+(.+)\s*·\s*(.+)\s*·\s*(\d+)\s*Wdh$/i', $line, $matches)) {
                $exerciseNumber = $matches[1];
                $exerciseName = mb_trim($matches[2]);
                $equipment = mb_trim($matches[3]);
                $targetReps = mb_trim($matches[4]);

                $this->info("Processing exercise: $exerciseName ($equipment)");

                // Map exercise name to actual exercise
                $mappedExerciseId = $this->exerciseMapping[$exerciseName];
                $currentExercise = Exercise::find($mappedExerciseId);

                if (! $currentExercise) {
                    $this->warn("Exercise not found: $mappedExerciseId");

                    continue;
                }

                $inSetsSection = true;

                continue;
            }

            // Check if this is the header for sets
            if ($inSetsSection && $line === '#;KG;WDH') {
                continue;
            }

            // Check if this is a set
            if ($inSetsSection && preg_match('/^(\d+);(.+);(\d+)$/i', $line, $matches)) {
                $setNumber = $matches[1];
                $weight = str_replace(',', '.', $matches[2]); // Convert decimal separator if needed
                $reps = $matches[3];

                if ($currentExercise) {
                    Set::create([
                        'workout_id' => $workout->id,
                        'exercise_id' => $currentExercise->id,
                        'reps' => $reps,
                        'weight' => (float) $weight,
                        'started_at' => null, // Individual set timings not available
                        'finished_at' => $workout->finished_at,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->info("  Added set: $setNumber, weight: $weight, reps: $reps");
                }
            }
        }
    }

    protected function parseDuration($durationLine): array
    {
        // Matches e.g. "1:20 Std" or "1:20"
        if (preg_match('/(\d+):(\d+)\s*Std/', (string) $durationLine, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];

            return [$hours, $minutes];
        }
        // Matches e.g. "57 Min"
        if (preg_match('/(\d+)\s*Min/i', (string) $durationLine, $matches)) {
            return [0, (int) $matches[1]];
        }
        // Matches e.g. "1 Std"
        if (preg_match('/(\d+)\s*Std/i', (string) $durationLine, $matches)) {
            return [(int) $matches[1], 0];
        }

        // Default fallback
        return [0, 0];
    }
}
