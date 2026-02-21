<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class ListQuests implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'List quests with optional filters. Can filter by: open (not completed), today (scheduled for today or earlier), daily, notes-only. Returns quest name, description, date, and completion status.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Quest::query();

        $filter = $request['filter'] ?? 'open';

        $query = match ($filter) {
            'open' => $query->notCompleted()->areNotNotes(),
            'today' => $query->notCompleted()->today()->areNotNotes(),
            'daily' => $query->notCompleted()->where('daily', true)->areNotNotes(),
            'completed' => $query->completed()->areNotNotes(),
            'notes' => $query->areNotes(),
            'all' => $query->areNotNotes(),
            default => $query->notCompleted()->areNotNotes(),
        };

        $results = $query->latest()->limit($request['limit'] ?? 20)->get();

        if ($results->isEmpty()) {
            return "No quests found with filter '$filter'.";
        }

        return $results->map(fn (Quest $quest) => sprintf(
            'ID: %d | Name: %s | Date: %s | Completed: %s',
            $quest->id,
            $quest->name,
            $quest->date?->format('Y-m-d') ?? 'none',
            $quest->isCompleted() ? 'Yes' : 'No',
        ))->implode("\n");
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'filter' => $schema->string(),
            'limit' => $schema->integer()->min(1)->max(50),
        ];
    }
}
