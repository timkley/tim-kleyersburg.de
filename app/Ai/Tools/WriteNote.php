<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Ai\Services\NotesService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use RuntimeException;
use Stringable;

class WriteNote implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: create or replace markdown note content at a file path. Invoke when user says: "Schreibe diese Notiz nach ...", "Aktualisiere die Datei ...", or requests full markdown content update. Do not invoke when: the user asks to edit quests, quest comments, or nutrition entries.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);

        try {
            $service->write($request['path'], $request['content']);
        } catch (RuntimeException $e) {
            return "Write failed: {$e->getMessage()}";
        }

        $result = $service->commitAndPush($request['path']);

        if (! $result['success']) {
            return "Written to {$request['path']}, but sync failed: {$result['output']}";
        }

        return "Written and synced: {$request['path']}";
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string()->required(),
            'content' => $schema->string()->required(),
        ];
    }
}
