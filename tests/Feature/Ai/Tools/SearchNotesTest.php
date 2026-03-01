<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\SearchNotes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    NotesService::resetPullDebounce();

    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir.'/Areas/Health', 0755, true);
    file_put_contents($this->testDir.'/Areas/Health/sleep.md', '# Sleep Tracking'.PHP_EOL.'Monitor sleep quality daily.');
    file_put_contents($this->testDir.'/Areas/Health/diet.md', '# Diet Notes'.PHP_EOL.'Track macros and meals.');

    app()->instance(NotesService::class, new NotesService($this->testDir));

    Process::fake([
        'git *' => Process::result(output: 'Already up to date.'),
    ]);
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
    NotesService::resetPullDebounce();
});

it('finds notes matching a query', function () {
    $tool = new SearchNotes;
    $result = $tool->handle(new Request(['query' => 'sleep']));

    expect($result)->toContain('sleep.md')
        ->toContain('Sleep Tracking');
});

it('returns no-results message for unmatched query', function () {
    $tool = new SearchNotes;
    $result = $tool->handle(new Request(['query' => 'nonexistent-xyzzy']));

    expect($result)->toContain('No notes found');
});

it('defines a schema with query and limit parameters', function () {
    $tool = new SearchNotes;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('query')
        ->toHaveKey('limit')
        ->and($schema['query'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['limit'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class);
});
