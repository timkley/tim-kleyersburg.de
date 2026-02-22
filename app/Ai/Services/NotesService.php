<?php

declare(strict_types=1);

namespace App\Ai\Services;

use DirectoryIterator;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class NotesService
{
    /**
     * Timestamp of the last pull operation, used for debouncing.
     */
    private static ?int $lastPullAt = null;

    /**
     * Create a new NotesService instance.
     */
    public function __construct(private string $basePath = '')
    {
        if ($this->basePath === '') {
            $this->basePath = storage_path('notes');
        }
    }

    /**
     * Reset the pull debounce timer (useful for testing).
     */
    public static function resetPullDebounce(): void
    {
        self::$lastPullAt = null;
    }

    /**
     * List directories and files at the given path, skipping .git.
     *
     * @return array{dirs: string[], files: string[]}
     */
    public function list(string $path = '/'): array
    {
        $resolved = $this->resolvePath($path);

        if ($resolved === null || ! is_dir($resolved)) {
            throw new RuntimeException("Invalid or non-existent directory: {$path}");
        }

        $dirs = [];
        $files = [];

        /** @var DirectoryIterator $item */
        foreach (new DirectoryIterator($resolved) as $item) {
            if ($item->isDot() || $item->getFilename() === '.git') {
                continue;
            }

            if ($item->isDir()) {
                $dirs[] = $item->getFilename();
            } else {
                $files[] = $item->getFilename();
            }
        }

        sort($dirs);
        sort($files);

        return ['dirs' => $dirs, 'files' => $files];
    }

    /**
     * Read a markdown file and return its contents.
     */
    public function read(string $path): string
    {
        $resolved = $this->resolvePath($path);

        if ($resolved === null || ! is_file($resolved)) {
            throw new RuntimeException("Invalid or non-existent file: {$path}");
        }

        return (string) file_get_contents($resolved);
    }

    /**
     * Write content to a file, creating parent directories as needed.
     */
    public function write(string $path, string $content): void
    {
        $relativePath = mb_ltrim($path, '/');
        $target = $this->basePath.'/'.$relativePath;
        $parentDir = dirname($target);

        if (! is_dir($parentDir)) {
            if (! mkdir($parentDir, 0755, true) && ! is_dir($parentDir)) {
                throw new RuntimeException("Failed to create directory: {$parentDir}");
            }
        }

        $resolvedParent = $this->resolvePath(dirname($relativePath));

        if ($resolvedParent === null) {
            throw new RuntimeException("Path escapes base directory: {$path}");
        }

        file_put_contents($resolvedParent.'/'.basename($target), $content);
    }

    /**
     * Search across all .md files for the given query, returning matches with context.
     *
     * @return array<int, array{file: string, line: int, text: string}>
     */
    public function search(string $query, int $limit = 10): array
    {
        $result = Process::path($this->basePath)->run([
            'grep', '-r', '-i', '-n', '-F', '--include=*.md', '-m', (string) $limit, $query, '.',
        ]);

        if ($result->exitCode() !== 0 && $result->exitCode() !== 1) {
            throw new RuntimeException("Search failed: {$result->errorOutput()}");
        }

        $matches = [];
        $lines = array_filter(explode("\n", mb_trim($result->output())));

        foreach ($lines as $line) {
            if (preg_match('/^\.\/(.+?):(\d+):(.*)$/', $line, $m)) {
                $matches[] = [
                    'file' => $m[1],
                    'line' => (int) $m[2],
                    'text' => mb_trim($m[3]),
                ];
            }

            if (count($matches) >= $limit) {
                break;
            }
        }

        return $matches;
    }

    /**
     * Pull latest changes from the remote repository, debounced to 60 seconds.
     *
     * @return array{success: bool, output: string}
     */
    public function pull(): array
    {
        if (self::$lastPullAt !== null && (time() - self::$lastPullAt) < 60) {
            return ['success' => true, 'output' => 'Debounced — pulled less than 60s ago.'];
        }

        $result = Process::path($this->basePath)->timeout(30)->run(['git', 'pull', '--rebase']);

        if ($result->successful()) {
            self::$lastPullAt = time();

            return ['success' => true, 'output' => mb_trim($result->output())];
        }

        Process::path($this->basePath)->run(['git', 'rebase', '--abort']);

        return ['success' => false, 'output' => mb_trim($result->errorOutput() ?: $result->output())];
    }

    /**
     * Stage, commit, and push changes for the given file path.
     *
     * @return array{success: bool, output: string}
     */
    public function commitAndPush(string $path): array
    {
        $resolved = $this->resolvePath($path);

        if ($resolved === null) {
            throw new RuntimeException("Invalid path: {$path}");
        }

        $relativePath = mb_ltrim($path, '/');

        $add = Process::path($this->basePath)->run(['git', 'add', $relativePath]);

        if ($add->failed()) {
            return ['success' => false, 'output' => mb_trim($add->errorOutput())];
        }

        $commit = Process::path($this->basePath)->run([
            'git', 'commit', '-m', "Update {$relativePath}",
        ]);

        if ($commit->failed()) {
            return ['success' => false, 'output' => mb_trim($commit->errorOutput() ?: $commit->output())];
        }

        $push = Process::path($this->basePath)->timeout(30)->run(['git', 'push']);

        if ($push->failed()) {
            return ['success' => false, 'output' => mb_trim($push->errorOutput())];
        }

        return ['success' => true, 'output' => mb_trim($commit->output())];
    }

    /**
     * Resolve a relative path within the base directory.
     * Returns null if the resolved path escapes the base directory.
     */
    private function resolvePath(string $path): ?string
    {
        $target = $this->basePath.'/'.mb_ltrim($path, '/');
        $real = realpath($target);

        if ($real === false) {
            return null;
        }

        if ($real !== $this->basePath && ! str_starts_with($real, $this->basePath.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $real;
    }
}
