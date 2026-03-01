<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use Illuminate\Process\FakeProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir, 0755, true);

    mkdir($this->testDir.'/projects', 0755, true);
    mkdir($this->testDir.'/journal', 0755, true);
    mkdir($this->testDir.'/.git', 0755, true);

    file_put_contents($this->testDir.'/README.md', '# My Notes');
    file_put_contents($this->testDir.'/projects/todo.md', '# Todo List');
    file_put_contents($this->testDir.'/journal/2024-01-15.md', "# Monday\nHad a great day.\nLearned about Laravel.");

    $this->service = new NotesService($this->testDir);
});

afterEach(function () {
    NotesService::resetPullDebounce();
    File::deleteDirectory($this->testDir);
});

it('lists directories and files at the root', function () {
    $result = $this->service->list('/');

    expect($result['dirs'])->toBe(['journal', 'projects'])
        ->and($result['files'])->toBe(['README.md']);
});

it('skips the .git directory when listing', function () {
    $result = $this->service->list('/');

    expect($result['dirs'])->not->toContain('.git');
});

it('lists files inside a subdirectory', function () {
    $result = $this->service->list('/projects');

    expect($result['dirs'])->toBe([])
        ->and($result['files'])->toBe(['todo.md']);
});

it('throws when listing a non-existent directory', function () {
    $this->service->list('/does-not-exist');
})->throws(RuntimeException::class, 'Invalid or non-existent directory');

it('reads a file at the root', function () {
    $content = $this->service->read('/README.md');

    expect($content)->toBe('# My Notes');
});

it('reads a file inside a subdirectory', function () {
    $content = $this->service->read('/projects/todo.md');

    expect($content)->toBe('# Todo List');
});

it('throws when reading a non-existent file', function () {
    $this->service->read('/missing.md');
})->throws(RuntimeException::class, 'Invalid or non-existent file');

it('writes a new file', function () {
    $this->service->write('/notes.md', '# New Note');

    expect(file_get_contents($this->testDir.'/notes.md'))->toBe('# New Note');
});

it('writes a file in a new nested directory', function () {
    $this->service->write('/deep/nested/note.md', '# Deep Note');

    expect(file_get_contents($this->testDir.'/deep/nested/note.md'))->toBe('# Deep Note');
});

it('overwrites an existing file', function () {
    $this->service->write('/README.md', '# Updated');

    expect(file_get_contents($this->testDir.'/README.md'))->toBe('# Updated');
});

it('searches across markdown files and returns matches', function () {
    $results = $this->service->search('Laravel');

    expect($results)->toHaveCount(1)
        ->and($results[0]['file'])->toBe('journal/2024-01-15.md')
        ->and($results[0]['text'])->toContain('Laravel');
});

it('returns empty results when search finds nothing', function () {
    $results = $this->service->search('nonexistent-unique-term');

    expect($results)->toBe([]);
});

it('searches case-insensitively', function () {
    $results = $this->service->search('laravel');

    expect($results)->toHaveCount(1);
});

it('respects the search limit', function () {
    file_put_contents($this->testDir.'/a.md', "line1 match\nline2 match\nline3 match");
    file_put_contents($this->testDir.'/b.md', "line1 match\nline2 match");

    $results = $this->service->search('match', 3);

    expect(count($results))->toBeLessThanOrEqual(3);
});

it('rejects path traversal via list', function () {
    $this->service->list('/../../../etc');
})->throws(RuntimeException::class);

it('rejects path traversal via read', function () {
    $this->service->read('/../../../etc/passwd');
})->throws(RuntimeException::class);

it('rejects path traversal via write', function () {
    $this->service->write('/../../../tmp/evil.txt', 'pwned');
})->throws(RuntimeException::class);

it('pulls latest changes', function () {
    Process::fake([
        '*git*pull*' => new FakeProcessResult(exitCode: 0, output: 'Already up to date.'),
    ]);

    $result = $this->service->pull();

    expect($result['success'])->toBeTrue()
        ->and($result['output'])->toBe('Already up to date.');

    Process::assertRan(fn ($process) => $process->command === ['git', 'pull', '--rebase']);
});

it('skips pull when debounced', function () {
    Process::fake([
        '*git*pull*' => new FakeProcessResult(exitCode: 0, output: 'Already up to date.'),
    ]);

    $this->service->pull();
    $result = $this->service->pull();

    expect($result['success'])->toBeTrue()
        ->and($result['output'])->toBe('Debounced — pulled less than 60s ago.');

    Process::assertRanTimes(fn ($process) => $process->command === ['git', 'pull', '--rebase'], 1);
});

it('aborts rebase on pull failure', function () {
    Process::fake([
        '*git*pull*' => new FakeProcessResult(exitCode: 1, errorOutput: 'CONFLICT'),
        '*git*rebase*' => new FakeProcessResult(exitCode: 0),
    ]);

    $result = $this->service->pull();

    expect($result['success'])->toBeFalse()
        ->and($result['output'])->toBe('CONFLICT');

    Process::assertRan(fn ($process) => $process->command === ['git', 'rebase', '--abort']);
});

it('commits and pushes a file', function () {
    Process::fake();

    $result = $this->service->commitAndPush('/README.md');

    expect($result['success'])->toBeTrue();

    Process::assertRan(fn ($process) => $process->command === ['git', 'add', 'README.md']);
    Process::assertRan(fn ($process) => $process->command === ['git', 'commit', '-m', 'Update README.md']);
    Process::assertRan(fn ($process) => $process->command === ['git', 'push']);
});

it('returns error when commit fails', function () {
    Process::fake([
        '*git*add*' => new FakeProcessResult(exitCode: 0),
        '*git*commit*' => new FakeProcessResult(exitCode: 1, output: 'nothing to commit'),
    ]);

    $result = $this->service->commitAndPush('/README.md');

    expect($result['success'])->toBeFalse()
        ->and($result['output'])->toBe('nothing to commit');
});

it('uses storage_path when no basePath is provided', function () {
    $service = new NotesService;

    $reflection = new ReflectionClass($service);
    $property = $reflection->getProperty('basePath');

    expect($property->getValue($service))->toBe(storage_path('notes'));
});

it('throws when mkdir fails during write', function () {
    // Create a file where a directory needs to be, making mkdir fail.
    // The @ suppresses the warning so we get the RuntimeException from the guard clause.
    $blockingFile = $this->testDir.'/blocker';
    file_put_contents($blockingFile, 'I block directory creation');

    // Override mkdir behavior by using a non-writable parent
    // Instead, we test the guard clause by making mkdir return false.
    // We need to trigger the condition: !mkdir(...) && !is_dir(...)
    set_error_handler(fn () => true); // Suppress ErrorException from mkdir warning

    try {
        $this->service->write('/blocker/sub/note.md', 'content');
    } finally {
        restore_error_handler();
    }
})->throws(RuntimeException::class, 'Failed to create directory');

it('throws when search grep command fails', function () {
    Process::fake([
        '*grep*' => new FakeProcessResult(exitCode: 2, errorOutput: 'grep: invalid option'),
    ]);

    $this->service->search('test');
})->throws(RuntimeException::class, 'Search failed');

it('throws when commitAndPush receives an invalid path', function () {
    $this->service->commitAndPush('/../../../etc/passwd');
})->throws(RuntimeException::class, 'Invalid path');

it('returns error when git add fails', function () {
    Process::fake([
        '*git*add*' => new FakeProcessResult(exitCode: 1, errorOutput: 'fatal: pathspec error'),
    ]);

    $result = $this->service->commitAndPush('/README.md');

    expect($result['success'])->toBeFalse()
        ->and($result['output'])->toBe('fatal: pathspec error');
});

it('returns error when git push fails', function () {
    Process::fake([
        '*git*add*' => new FakeProcessResult(exitCode: 0),
        '*git*commit*' => new FakeProcessResult(exitCode: 0, output: 'committed'),
        '*git*push*' => new FakeProcessResult(exitCode: 1, errorOutput: 'rejected: non-fast-forward'),
    ]);

    $result = $this->service->commitAndPush('/README.md');

    expect($result['success'])->toBeFalse()
        ->and($result['output'])->toBe('rejected: non-fast-forward');
});
