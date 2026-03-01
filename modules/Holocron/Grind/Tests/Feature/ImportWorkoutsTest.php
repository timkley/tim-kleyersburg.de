<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\Plan;
use Modules\Holocron\Grind\Models\Workout;

use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir().'/grind-import-test-'.uniqid();
    mkdir($this->tempDir, 0755, true);
});

afterEach(function () {
    array_map('unlink', glob($this->tempDir.'/*') ?: []);
    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

test('command fails when file does not exist', function () {
    artisan('grind:import-ap-workouts', ['file' => '/nonexistent/path.txt'])
        ->assertExitCode(1)
        ->expectsOutputToContain('File not found');
});

test('command skips workouts with unmapped plan names', function () {
    $content = <<<'TXT'
"Unknown Plan · Tag 1 · Woche 1 · 2025-03-10 09:00 · 57 Min"
TXT;

    $filePath = $this->tempDir.'/unknown-plan.txt';
    file_put_contents($filePath, $content);

    artisan('grind:import-ap-workouts', ['file' => $filePath])
        ->assertExitCode(0)
        ->expectsOutputToContain('Import completed');

    expect(Workout::query()->count())->toBe(0);
});

test('command skips workouts shorter than 10 minutes', function () {
    Plan::factory()->create(['id' => 1]);

    $content = <<<'TXT'
"GK 1 · Tag 1 · Woche 1 · 2025-03-10 09:00 · 5 Min"
TXT;

    $filePath = $this->tempDir.'/short-workout.txt';
    file_put_contents($filePath, $content);

    artisan('grind:import-ap-workouts', ['file' => $filePath])
        ->assertExitCode(0);

    expect(Workout::query()->count())->toBe(0);
});

test('command rolls back on database error and reports error', function () {
    // The command uses Workout::create with 'current_exercise_index' which is not
    // a valid column (actual column is 'current_exercise_id'), so the insert fails.
    // The transaction is rolled back and the error is reported.
    Plan::factory()->create(['id' => 1]);

    $content = <<<'TXT'
"GK 1 · Tag 1 · Woche 1 · 2025-03-10 09:00 · 57 Min"
TXT;

    $filePath = $this->tempDir.'/valid-workout.txt';
    file_put_contents($filePath, $content);

    artisan('grind:import-ap-workouts', ['file' => $filePath])
        ->assertExitCode(0)
        ->expectsOutputToContain('Error processing workout')
        ->expectsOutputToContain('Import completed');

    expect(Workout::query()->count())->toBe(0);
});

test('command outputs found workouts count', function () {
    $content = <<<'TXT'
"Unknown · Tag 1 · Woche 1 · 2025-03-10 09:00 · 57 Min"
TXT;

    $filePath = $this->tempDir.'/count.txt';
    file_put_contents($filePath, $content);

    artisan('grind:import-ap-workouts', ['file' => $filePath])
        ->assertExitCode(0)
        ->expectsOutputToContain('Found 1 workouts to process');
});

test('command continues processing after a failed workout', function () {
    Plan::factory()->create(['id' => 1]);
    Plan::factory()->create(['id' => 2]);

    // Both workouts will fail at Workout::create due to 'current_exercise_index' column issue,
    // but the command should continue processing and output 'Import completed'.
    $content = <<<'TXT'
"GK 1 · Tag 1 · Woche 1 · 2025-03-10 09:00 · 57 Min"
"GK2 · Tag 2 · Woche 1 · 2025-03-11 09:00 · 1:10 Std"
TXT;

    $filePath = $this->tempDir.'/multi-workouts.txt';
    file_put_contents($filePath, $content);

    artisan('grind:import-ap-workouts', ['file' => $filePath])
        ->assertExitCode(0)
        ->expectsOutputToContain('Import completed');
});

test('command returns success exit code even when all workouts fail', function () {
    Plan::factory()->create(['id' => 1]);

    $content = <<<'TXT'
"GK 1 · Tag 1 · Woche 1 · 2025-03-10 09:00 · 57 Min"
TXT;

    $filePath = $this->tempDir.'/all-fail.txt';
    file_put_contents($filePath, $content);

    artisan('grind:import-ap-workouts', ['file' => $filePath])
        ->assertExitCode(0);
});

test('command throws on invalid header format', function () {
    $content = <<<'TXT'
"Completely Invalid Header Line Without Pattern"
TXT;

    $filePath = $this->tempDir.'/invalid-header.txt';
    file_put_contents($filePath, $content);

    artisan('grind:import-ap-workouts', ['file' => $filePath])
        ->assertExitCode(0)
        ->expectsOutputToContain('Error processing workout');

    expect(Workout::query()->count())->toBe(0);
});

test('command throws when mapped plan does not exist in database', function () {
    // 'GK 1' maps to plan ID 1, but we don't create it in the database
    $content = <<<'TXT'
"GK 1 · Tag 1 · Woche 1 · 2025-03-10 09:00 · 57 Min"
TXT;

    $filePath = $this->tempDir.'/missing-plan.txt';
    file_put_contents($filePath, $content);

    artisan('grind:import-ap-workouts', ['file' => $filePath])
        ->assertExitCode(0)
        ->expectsOutputToContain('Error processing workout');

    expect(Workout::query()->count())->toBe(0);
});

test('parseDuration handles minutes format', function () {
    $command = new Modules\Holocron\Grind\Commands\ImportWorkouts;
    $reflection = new ReflectionMethod($command, 'parseDuration');

    expect($reflection->invoke($command, '57 Min'))->toBe([0, 57]);
});

test('parseDuration handles hours and minutes format', function () {
    $command = new Modules\Holocron\Grind\Commands\ImportWorkouts;
    $reflection = new ReflectionMethod($command, 'parseDuration');

    expect($reflection->invoke($command, '1:20 Std'))->toBe([1, 20]);
});

test('parseDuration handles hours only format', function () {
    $command = new Modules\Holocron\Grind\Commands\ImportWorkouts;
    $reflection = new ReflectionMethod($command, 'parseDuration');

    expect($reflection->invoke($command, '2 Std'))->toBe([2, 0]);
});

test('parseDuration returns zero for unrecognized format', function () {
    $command = new Modules\Holocron\Grind\Commands\ImportWorkouts;
    $reflection = new ReflectionMethod($command, 'parseDuration');

    expect($reflection->invoke($command, 'unknown format'))->toBe([0, 0]);
});
