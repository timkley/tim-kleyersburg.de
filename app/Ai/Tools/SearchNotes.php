<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Note;
use Stringable;

class SearchNotes implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search notes/comments on quests using semantic/vector search. Returns matching notes with their content and associated quest.';
    }

    /**
     * Execute the tool.
     */
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

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
            'limit' => $schema->integer()->min(1)->max(10),
        ];
    }
}
