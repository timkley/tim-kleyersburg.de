<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use RuntimeException;
use Stringable;

class BrowseNotes implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: browse knowledge-base folders and file names. Invoke when user says: "Zeig die Ordner", "Was liegt unter /Areas?", or asks for directory listings. Do not invoke when: the user asks for full-text search results or full file content.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);
        $service->pull();

        try {
            $result = $service->list($request['path'] ?? '/');
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }

        $output = [];
        foreach ($result['dirs'] as $dir) {
            $output[] = "\u{1F4C1} {$dir}/";
        }
        foreach ($result['files'] as $file) {
            $output[] = "\u{1F4C4} {$file}";
        }

        return implode("\n", $output) ?: 'Empty directory.';
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string(),
        ];
    }
}
