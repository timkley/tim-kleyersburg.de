<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use Illuminate\Support\Facades\File;

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
