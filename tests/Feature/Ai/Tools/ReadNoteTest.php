<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\ReadNote;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    NotesService::resetPullDebounce();
    Process::fake();

    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir.'/Areas', 0755, true);
    file_put_contents($this->testDir.'/Areas/test.md', '# Test Note'.PHP_EOL.'Some content here.');

    app()->instance(NotesService::class, new NotesService($this->testDir));
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
});

it('reads a markdown file', function () {
    $tool = new ReadNote;
    $result = $tool->handle(new Request(['path' => '/Areas/test.md']));

    expect($result)->toContain('# Test Note')
        ->toContain('Some content here.');
});

it('returns error for non-existent file', function () {
    $tool = new ReadNote;
    $result = $tool->handle(new Request(['path' => '/nope.md']));

    expect($result)->toContain('non-existent');
});

it('defines a schema with a path parameter', function () {
    $tool = new ReadNote;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('path')
        ->and($schema['path'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class);
});
