# Notes Repo Integration Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Integrate an external PARA-structured markdown notes repo as a git submodule with Laravel AI tools so Chopper can browse, read, write, and search notes.

**Architecture:** Git submodule at `storage/notes/`, a `NotesService` handling filesystem + git sync (debounced pull before reads, auto commit+push after writes), 4 new AI tools (`BrowseNotes`, `ReadNote`, `WriteNote`, `SearchNotes`), and rename of existing `SearchNotes` to `SearchQuestComments`.

**Tech Stack:** Laravel AI Tools, PHP Process (for git commands), Pest for testing

**Design doc:** `docs/plans/2026-02-22-notes-repo-integration-design.md`

---

### Task 1: Rename SearchNotes to SearchQuestComments

**Files:**
- Rename: `app/Ai/Tools/SearchNotes.php` -> `app/Ai/Tools/SearchQuestComments.php`
- Modify: `app/Ai/Agents/ChopperAgent.php:14,71`

**Step 1: Rename the file and update class name**

Rename `app/Ai/Tools/SearchNotes.php` to `app/Ai/Tools/SearchQuestComments.php`. Update the class name inside to `SearchQuestComments`. Keep everything else identical.

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Note;
use Stringable;

class SearchQuestComments implements Tool
{
    public function description(): Stringable|string
    {
        return 'Search notes/comments on quests using semantic/vector search. Returns matching notes with their content and associated quest.';
    }

    public function handle(Request $request): Stringable|string
    {
        $results = Note::search($request['query'])->options([
            'query_by' => 'embedding',
            'prefix' => false,
            'drop_tokens_threshold' => 0,
            'per_page' => $request['limit'] ?? 5,
        ])->get()->take($request['limit'] ?? 5)->load('quest');

        if ($results->isEmpty()) {
            return 'No notes found matching the query.';
        }

        return $results->map(fn (Note $note) => sprintf(
            'Note ID: %d | Quest: %s (ID: %d) | Content: %s',
            $note->id,
            $note->quest->name,
            $note->quest_id,
            str($note->content)->stripTags()->limit(300),
        ))->implode("\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'limit' => $schema->integer()->min(1)->max(10),
        ];
    }
}
```

**Step 2: Update ChopperAgent import and registration**

In `app/Ai/Agents/ChopperAgent.php`:
- Change `use App\Ai\Tools\SearchNotes;` to `use App\Ai\Tools\SearchQuestComments;`
- Change `new SearchNotes,` to `new SearchQuestComments,` in `tools()`

**Step 3: Delete old file**

Delete `app/Ai/Tools/SearchNotes.php`.

**Step 4: Run static analysis**

Run: `composer phpstan`
Expected: PASS (no references to old class name)

**Step 5: Commit**

```bash
git add -A && git commit -m "refactor: rename SearchNotes to SearchQuestComments"
```

---

### Task 2: Add git submodule

**Step 1: Add the submodule**

Ask the user for the git URL of their notes repo, then run:

```bash
git submodule add <NOTES_REPO_URL> storage/notes
```

**Step 2: Verify the submodule**

```bash
ls storage/notes/
```

Expected: PARA folder structure visible (Areas/, Projects/, Resources/, Archive/ or similar)

**Step 3: Commit**

```bash
git add .gitmodules storage/notes && git commit -m "chore: add notes repo as git submodule at storage/notes"
```

---

### Task 3: NotesService — filesystem operations

**Files:**
- Create: `app/Ai/Services/NotesService.php`
- Test: `tests/Feature/Ai/Services/NotesServiceTest.php`

**Step 1: Write the failing tests**

Create `tests/Feature/Ai/Services/NotesServiceTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir, 0755, true);
    mkdir($this->testDir.'/Areas/Health', 0755, true);
    file_put_contents($this->testDir.'/Areas/Health/sleep.md', '# Sleep Notes'.PHP_EOL.'Track sleep patterns here.');
    file_put_contents($this->testDir.'/README.md', '# My Notes');

    $this->service = new NotesService($this->testDir);
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
});

it('lists directories and files at the root', function () {
    $result = $this->service->list('/');

    expect($result)->toContain('Areas/')
        ->toContain('README.md');
});

it('lists contents of a subdirectory', function () {
    $result = $this->service->list('/Areas');

    expect($result)->toContain('Health/');
});

it('lists files in a nested directory', function () {
    $result = $this->service->list('/Areas/Health');

    expect($result)->toContain('sleep.md');
});

it('reads a markdown file', function () {
    $content = $this->service->read('/Areas/Health/sleep.md');

    expect($content)->toContain('# Sleep Notes')
        ->toContain('Track sleep patterns here.');
});

it('returns error for non-existent file', function () {
    $content = $this->service->read('/nope.md');

    expect($content)->toContain('not found');
});

it('writes a new file and creates parent directories', function () {
    $this->service->write('/Projects/App/ideas.md', '# App Ideas');

    expect(file_get_contents($this->testDir.'/Projects/App/ideas.md'))
        ->toBe('# App Ideas');
});

it('overwrites an existing file', function () {
    $this->service->write('/README.md', '# Updated Notes');

    expect(file_get_contents($this->testDir.'/README.md'))
        ->toBe('# Updated Notes');
});

it('searches for content across markdown files', function () {
    $results = $this->service->search('sleep');

    expect($results)->toContain('Areas/Health/sleep.md')
        ->toContain('Sleep Notes');
});

it('returns no-results message when search has no matches', function () {
    $results = $this->service->search('nonexistent-xyzzy');

    expect($results)->toContain('No notes found');
});

it('rejects paths that try to escape the base directory', function () {
    $content = $this->service->read('/../../../etc/passwd');

    expect($content)->toContain('Invalid path');
});
```

**Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=NotesServiceTest`
Expected: FAIL (class not found)

**Step 3: Create NotesService**

Create `app/Ai/Services/NotesService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Services;

use Illuminate\Support\Facades\Process;
use RuntimeException;

class NotesService
{
    private float $lastPullAt = 0;

    private const PULL_DEBOUNCE_SECONDS = 60;

    public function __construct(
        private string $basePath = '',
    ) {
        if ($this->basePath === '') {
            $this->basePath = storage_path('notes');
        }
    }

    /**
     * List directories and files at the given path.
     */
    public function list(string $path = '/'): string
    {
        $resolved = $this->resolvePath($path);
        if ($resolved === null) {
            return 'Invalid path.';
        }

        if (! is_dir($resolved)) {
            return "Directory not found: $path";
        }

        $items = scandir($resolved);
        $entries = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '.git') {
                continue;
            }

            $entries[] = is_dir($resolved.'/'.$item) ? $item.'/' : $item;
        }

        if (empty($entries)) {
            return "Empty directory: $path";
        }

        sort($entries);

        return implode("\n", $entries);
    }

    /**
     * Read the content of a markdown file.
     */
    public function read(string $path): string
    {
        $resolved = $this->resolvePath($path);
        if ($resolved === null) {
            return 'Invalid path.';
        }

        if (! is_file($resolved)) {
            return "File not found: $path";
        }

        return file_get_contents($resolved);
    }

    /**
     * Write content to a markdown file, creating parent directories as needed.
     */
    public function write(string $path, string $content): string
    {
        $resolved = $this->resolvePath($path);
        if ($resolved === null) {
            return 'Invalid path.';
        }

        $dir = dirname($resolved);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($resolved, $content);

        return "Written: $path";
    }

    /**
     * Full-text search across all markdown files.
     */
    public function search(string $query, int $limit = 10): string
    {
        $result = Process::path($this->basePath)
            ->run(['grep', '-ril', '--include=*.md', $query, '.']);

        if (! $result->successful() || trim($result->output()) === '') {
            return 'No notes found matching: '.$query;
        }

        $files = array_slice(
            array_filter(explode("\n", trim($result->output()))),
            0,
            $limit,
        );

        $output = [];
        foreach ($files as $file) {
            $relativePath = ltrim($file, './');
            $contextResult = Process::path($this->basePath)
                ->run(['grep', '-n', '-m', '3', '-i', $query, $file]);

            $context = trim($contextResult->output());
            $output[] = "--- $relativePath ---\n$context";
        }

        return implode("\n\n", $output);
    }

    /**
     * Pull latest changes from remote (debounced).
     */
    public function pull(): string
    {
        $now = microtime(true);
        if (($now - $this->lastPullAt) < self::PULL_DEBOUNCE_SECONDS) {
            return 'Skipped pull (debounced).';
        }

        if (! is_dir($this->basePath.'/.git')) {
            return 'Not a git repository.';
        }

        $result = Process::path($this->basePath)
            ->run(['git', 'pull', '--rebase']);

        if (! $result->successful()) {
            Process::path($this->basePath)->run(['git', 'rebase', '--abort']);

            return 'Pull failed (conflict?): '.$result->errorOutput().' — Rebase aborted. Resolve manually.';
        }

        $this->lastPullAt = $now;

        return 'Pulled latest changes.';
    }

    /**
     * Commit and push a file change.
     */
    public function commitAndPush(string $path): string
    {
        if (! is_dir($this->basePath.'/.git')) {
            return 'Not a git repository.';
        }

        $relativePath = ltrim($path, '/');

        Process::path($this->basePath)->run(['git', 'add', $relativePath]);

        $commitResult = Process::path($this->basePath)
            ->run(['git', 'commit', '-m', "Update $relativePath"]);

        if (! $commitResult->successful()) {
            return 'Commit failed: '.$commitResult->errorOutput();
        }

        $pushResult = Process::path($this->basePath)->run(['git', 'push']);

        if (! $pushResult->successful()) {
            return 'Committed but push failed: '.$pushResult->errorOutput();
        }

        return "Committed and pushed: $relativePath";
    }

    /**
     * Resolve a relative path to an absolute path within the base directory.
     * Returns null if the path escapes the base directory.
     */
    private function resolvePath(string $path): ?string
    {
        $path = ltrim($path, '/');
        $full = $this->basePath.'/'.$path;
        $real = realpath($full);

        // For new files, realpath returns false — check the parent directory instead
        if ($real === false) {
            $parentReal = realpath(dirname($full));
            if ($parentReal === false || ! str_starts_with($parentReal, $this->basePath)) {
                return null;
            }

            return $parentReal.'/'.basename($full);
        }

        if (! str_starts_with($real, $this->basePath)) {
            return null;
        }

        return $real;
    }
}
```

**Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter=NotesServiceTest`
Expected: PASS

**Step 5: Run static analysis and formatter**

Run: `composer phpstan && vendor/bin/pint --dirty --format agent`

**Step 6: Commit**

```bash
git add app/Ai/Services/NotesService.php tests/Feature/Ai/Services/NotesServiceTest.php
git commit -m "feat: add NotesService for filesystem and git sync operations"
```

---

### Task 4: BrowseNotes AI tool

**Files:**
- Create: `app/Ai/Tools/BrowseNotes.php`
- Test: `tests/Feature/Ai/Tools/BrowseNotesTest.php`

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Tools/BrowseNotesTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\BrowseNotes;
use Illuminate\Support\Facades\File;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
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
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=BrowseNotesTest`
Expected: FAIL

**Step 3: Create BrowseNotes tool**

Create `app/Ai/Tools/BrowseNotes.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class BrowseNotes implements Tool
{
    public function description(): Stringable|string
    {
        return 'Browse the knowledge base directory structure. Lists folders and markdown files at a given path. Use "/" for the root.';
    }

    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);
        $service->pull();

        return $service->list($request['path'] ?? '/');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string(),
        ];
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=BrowseNotesTest`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Ai/Tools/BrowseNotes.php tests/Feature/Ai/Tools/BrowseNotesTest.php
git commit -m "feat: add BrowseNotes AI tool"
```

---

### Task 5: ReadNote AI tool

**Files:**
- Create: `app/Ai/Tools/ReadNote.php`
- Test: `tests/Feature/Ai/Tools/ReadNoteTest.php`

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Tools/ReadNoteTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\ReadNote;
use Illuminate\Support\Facades\File;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
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

    expect($result)->toContain('not found');
});
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=ReadNoteTest`
Expected: FAIL

**Step 3: Create ReadNote tool**

Create `app/Ai/Tools/ReadNote.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ReadNote implements Tool
{
    public function description(): Stringable|string
    {
        return 'Read the content of a markdown note from the knowledge base. Provide the file path relative to the notes root (e.g. "Areas/Health/sleep.md").';
    }

    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);
        $service->pull();

        return $service->read($request['path']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()->required(),
        ];
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=ReadNoteTest`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Ai/Tools/ReadNote.php tests/Feature/Ai/Tools/ReadNoteTest.php
git commit -m "feat: add ReadNote AI tool"
```

---

### Task 6: WriteNote AI tool

**Files:**
- Create: `app/Ai/Tools/WriteNote.php`
- Test: `tests/Feature/Ai/Tools/WriteNoteTest.php`

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Tools/WriteNoteTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\WriteNote;
use Illuminate\Support\Facades\File;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir, 0755, true);

    app()->instance(NotesService::class, new NotesService($this->testDir));
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
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
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=WriteNoteTest`
Expected: FAIL

**Step 3: Create WriteNote tool**

Create `app/Ai/Tools/WriteNote.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class WriteNote implements Tool
{
    public function description(): Stringable|string
    {
        return 'Create or update a markdown note in the knowledge base. Provide the file path and full content. Auto-commits and pushes the change.';
    }

    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);

        $result = $service->write($request['path'], $request['content']);
        $service->commitAndPush($request['path']);

        return $result;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()->required(),
            'content' => $schema->string()->required(),
        ];
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=WriteNoteTest`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Ai/Tools/WriteNote.php tests/Feature/Ai/Tools/WriteNoteTest.php
git commit -m "feat: add WriteNote AI tool"
```

---

### Task 7: SearchNotes AI tool

**Files:**
- Create: `app/Ai/Tools/SearchNotes.php`
- Test: `tests/Feature/Ai/Tools/SearchNotesTest.php`

**Step 1: Write the failing test**

Create `tests/Feature/Ai/Tools/SearchNotesTest.php`:

```php
<?php

declare(strict_types=1);

use App\Ai\Services\NotesService;
use App\Ai\Tools\SearchNotes;
use Illuminate\Support\Facades\File;
use Laravel\Ai\Tools\Request;

beforeEach(function () {
    $this->testDir = storage_path('notes-test-'.uniqid());
    mkdir($this->testDir.'/Areas/Health', 0755, true);
    file_put_contents($this->testDir.'/Areas/Health/sleep.md', '# Sleep Tracking'.PHP_EOL.'Monitor sleep quality daily.');
    file_put_contents($this->testDir.'/Areas/Health/diet.md', '# Diet Notes'.PHP_EOL.'Track macros and meals.');

    app()->instance(NotesService::class, new NotesService($this->testDir));
});

afterEach(function () {
    File::deleteDirectory($this->testDir);
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
```

**Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=SearchNotesTest`
Expected: FAIL

**Step 3: Create the new SearchNotes tool**

Create `app/Ai/Tools/SearchNotes.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchNotes implements Tool
{
    public function description(): Stringable|string
    {
        return 'Full-text search across all markdown notes in the knowledge base. Returns matching files with context lines.';
    }

    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);
        $service->pull();

        return $service->search($request['query'], $request['limit'] ?? 10);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'limit' => $schema->integer()->min(1)->max(20),
        ];
    }
}
```

**Step 4: Run tests**

Run: `php artisan test --compact --filter=SearchNotesTest`
Expected: PASS

**Step 5: Commit**

```bash
git add app/Ai/Tools/SearchNotes.php tests/Feature/Ai/Tools/SearchNotesTest.php
git commit -m "feat: add SearchNotes AI tool for knowledge base"
```

---

### Task 8: Register tools in ChopperAgent and update instructions

**Files:**
- Modify: `app/Ai/Agents/ChopperAgent.php`

**Step 1: Update imports and tools array**

Add imports for the 4 new tools and register them in `tools()`. Update the instructions to mention the knowledge base.

In `app/Ai/Agents/ChopperAgent.php`, add these imports:

```php
use App\Ai\Tools\BrowseNotes;
use App\Ai\Tools\ReadNote;
use App\Ai\Tools\WriteNote;
```

Update the `tools()` method to include the new tools (note: `SearchNotes` is already imported but now points to the new class):

```php
public function tools(): iterable
{
    return [
        new SearchQuests,
        new SearchQuestComments,
        new ListQuests,
        new GetQuest,
        new CreateQuest,
        new CompleteQuest,
        new AddNoteToQuest,
        new BrowseNotes,
        new ReadNote,
        new WriteNote,
        new SearchNotes,
        new LogMeal,
        new QueryNutrition,
    ];
}
```

Update the instructions to add a paragraph about the knowledge base:

```
Du hast Zugriff auf eine Wissensdatenbank (Knowledge Base) mit Markdown-Notizen, organisiert nach dem PARA-Prinzip (Projects, Areas, Resources, Archive). Du kannst Notizen durchsuchen, lesen, erstellen und bearbeiten.
```

**Step 2: Run full test suite**

Run: `php artisan test --compact`
Expected: PASS

**Step 3: Run static analysis and formatter**

Run: `composer phpstan && vendor/bin/pint --dirty --format agent`
Expected: PASS

**Step 4: Commit**

```bash
git add app/Ai/Agents/ChopperAgent.php
git commit -m "feat: register knowledge base tools in ChopperAgent"
```

---

### Task 9: Final verification

**Step 1: Run full test suite**

Run: `php artisan test --compact`
Expected: ALL PASS

**Step 2: Run static analysis**

Run: `composer phpstan`
Expected: PASS

**Step 3: Run code style**

Run: `vendor/bin/pint --dirty --format agent`
Expected: PASS or auto-fixed
