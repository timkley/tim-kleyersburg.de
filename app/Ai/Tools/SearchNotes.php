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
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: run full-text search across markdown knowledge-base notes. Invoke when user says: "Suche in Notizen nach ...", "Finde Notes mit ...", or asks for matching files by term. Do not invoke when: the user asks for quest records, quest comments, or nutrition data.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $service = app(NotesService::class);
        $service->pull();

        $results = $service->search($request['query'], $request['limit'] ?? 10);

        if ($results === []) {
            return 'No notes found matching the query.';
        }

        $lines = [];
        foreach ($results as $match) {
            $lines[] = sprintf('%s:%d — %s', $match['file'], $match['line'], $match['text']);
        }

        return implode("\n", $lines);
    }

    /**
     * Get the tool's schema definition.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'limit' => $schema->integer()->min(1)->max(20),
        ];
    }
}
