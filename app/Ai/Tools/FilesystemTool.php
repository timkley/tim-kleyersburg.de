<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use RuntimeException;
use Stringable;

class FilesystemTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Manage knowledge base files (PARA-organized markdown notes). Actions: browse (list directory), read (get file content), write (create/update file with auto git sync), search (full-text search across all notes).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);
        $action = $request['action'];

        try {
            return match ($action) {
                'browse' => $this->browse($service, $request['path'] ?? '/'),
                'read' => $this->read($service, $request['path'] ?? '/'),
                'write' => $this->write($service, $request['path'] ?? '/', $request['content'] ?? ''),
                'search' => $this->search($service, $request['query'] ?? ''),
                default => "Unknown action: {$action}. Use browse, read, write, or search.",
            };
        } catch (RuntimeException $e) {
            return "Error: {$e->getMessage()}";
        }
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->required(),
            'path' => $schema->string(),
            'content' => $schema->string(),
            'query' => $schema->string(),
        ];
    }

    private function browse(NotesService $service, string $path): string
    {
        $service->pull();
        $listing = $service->list($path);

        $output = "Directory: {$path}\n\n";

        foreach ($listing['dirs'] as $dir) {
            $output .= "{$dir}/\n";
        }

        foreach ($listing['files'] as $file) {
            $output .= "{$file}\n";
        }

        return $output;
    }

    private function read(NotesService $service, string $path): string
    {
        return $service->read($path);
    }

    private function write(NotesService $service, string $path, string $content): string
    {
        $service->write($path, $content);
        $result = $service->commitAndPush($path);

        if ($result['success']) {
            return "File written and synced: {$path}";
        }

        return "File written but sync failed: {$result['output']}";
    }

    private function search(NotesService $service, string $query): string
    {
        $matches = $service->search($query);

        if ($matches === []) {
            return "No results found for: {$query}";
        }

        $output = "Search results for '{$query}':\n\n";

        foreach ($matches as $match) {
            $output .= "{$match['file']}:{$match['line']} — {$match['text']}\n";
        }

        return $output;
    }
}
