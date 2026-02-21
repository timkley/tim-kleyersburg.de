<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class SearchQuests implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search quests using semantic/vector search. Returns matching quests with their name, description, completion status, and date.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $results = Quest::search($request['query'])->options([
            'query_by' => 'embedding',
            'prefix' => false,
            'drop_tokens_threshold' => 0,
            'per_page' => $request['limit'] ?? 5,
        ])->get()->take($request['limit'] ?? 5);

        if ($results->isEmpty()) {
            return 'No quests found matching the query.';
        }

        return $results->map(fn (Quest $quest) => sprintf(
            'ID: %d | Name: %s | Description: %s | Completed: %s | Date: %s',
            $quest->id,
            $quest->name,
            str($quest->description)->stripTags()->limit(200),
            $quest->isCompleted() ? 'Yes' : 'No',
            $quest->date?->format('Y-m-d') ?? 'none',
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
