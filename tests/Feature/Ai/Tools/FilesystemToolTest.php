<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\FilesystemTool;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    NotesService::resetPullDebounce();

    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir.'/Projects', 0755, true);
    file_put_contents($this->testDir.'/Projects/test.md', '# Test Note');

    app()->instance(NotesService::class, new NotesService($this->testDir));
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
    NotesService::resetPullDebounce();
});

it('browses a directory', function () {
    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'browse',
        'path' => '/',
    ]));

    expect($result)->toContain('Projects');
});

it('reads a file', function () {
    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'read',
        'path' => '/Projects/test.md',
    ]));

    expect($result)->toContain('# Test Note');
});

it('searches for content', function () {
    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'search',
        'query' => 'Test Note',
    ]));

    expect($result)->toContain('test.md');
});

it('writes a file and reports sync result', function () {
    Process::fake();

    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'write',
        'path' => '/Projects/new.md',
        'content' => '# New Note',
    ]));

    expect($result)->toContain('/Projects/new.md')
        ->and(file_get_contents($this->testDir.'/Projects/new.md'))->toBe('# New Note');
});

it('returns error for unknown action', function () {
    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'delete',
    ]));

    expect($result)->toContain('Unknown action')
        ->toContain('delete');
});

it('returns error for invalid path', function () {
    $tool = new FilesystemTool;

    $result = (string) $tool->handle(new Request([
        'action' => 'read',
        'path' => '/nonexistent/file.md',
    ]));

    expect($result)->toContain('Error');
});

it('returns the expected schema definition', function () {
    $tool = new FilesystemTool;

    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKeys(['action', 'path', 'content', 'query']);
});
