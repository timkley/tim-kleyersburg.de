<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\WriteNote;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    NotesService::resetPullDebounce();

    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir, 0755, true);

    app()->instance(NotesService::class, new NotesService($this->testDir));

    Process::fake();
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
    NotesService::resetPullDebounce();
});

it('creates a new note file', function () {
    $tool = new WriteNote;
    $result = $tool->handle(new Request([
        'path' => '/Projects/app/ideas.md',
        'content' => '# App Ideas',
    ]));

    expect($result)->toContain('Written')
        ->and(file_get_contents($this->testDir.'/Projects/app/ideas.md'))
        ->toBe('# App Ideas');
});

it('overwrites an existing note file', function () {
    mkdir($this->testDir.'/Areas', 0755, true);
    file_put_contents($this->testDir.'/Areas/old.md', 'old content');

    $tool = new WriteNote;
    $result = $tool->handle(new Request([
        'path' => '/Areas/old.md',
        'content' => '# Updated',
    ]));

    expect($result)->toContain('Written')
        ->and(file_get_contents($this->testDir.'/Areas/old.md'))
        ->toBe('# Updated');
});
