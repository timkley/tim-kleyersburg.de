<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\BrowseNotes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    NotesService::resetPullDebounce();
    Process::fake();

    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir.'/Areas/Health', 0755, true);
    file_put_contents($this->testDir.'/README.md', '# Notes');

    app()->instance(NotesService::class, new NotesService($this->testDir));
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
});

it('lists root directory contents', function () {
    $tool = new BrowseNotes;
    $result = $tool->handle(new Request([]));

    expect($result)->toContain('Areas/')
        ->toContain('README.md');
});

it('lists subdirectory contents', function () {
    $tool = new BrowseNotes;
    $result = $tool->handle(new Request(['path' => '/Areas']));

    expect($result)->toContain('Health/');
});

it('returns error for non-existent path', function () {
    $tool = new BrowseNotes;
    $result = $tool->handle(new Request(['path' => '/nope']));

    expect($result)->toContain('non-existent');
});

it('defines a schema with a path parameter', function () {
    $tool = new BrowseNotes;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('path')
        ->and($schema['path'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class);
});
