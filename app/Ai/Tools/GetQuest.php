<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class GetQuest implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get detailed information about a specific quest by ID, including its children (sub-quests) and notes/comments.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $quest = Quest::with(['children', 'notes'])->find($request['quest_id']);

        if (! $quest) {
            return "Quest with ID {$request['quest_id']} not found.";
        }

        $output = sprintf(
            "Quest ID: %d\nName: %s\nDescription: %s\nDate: %s\nCompleted: %s\nIs Note: %s\nDaily: %s",
            $quest->id,
            $quest->name,
            str($quest->description)->stripTags(),
            $quest->date?->format('Y-m-d') ?? 'none',
            $quest->isCompleted() ? 'Yes' : 'No',
            $quest->is_note ? 'Yes' : 'No',
            $quest->daily ? 'Yes' : 'No',
        );

        if ($quest->children->isNotEmpty()) {
            $output .= "\n\nSub-quests:";
            foreach ($quest->children as $child) {
                $output .= sprintf(
                    "\n  - ID: %d | %s | Completed: %s",
                    $child->id,
                    $child->name,
                    $child->isCompleted() ? 'Yes' : 'No',
                );
            }
        }

        if ($quest->notes->isNotEmpty()) {
            $output .= "\n\nNotes:";
            foreach ($quest->notes as $note) {
                $output .= sprintf(
                    "\n  - [%s] %s (%s)",
                    $note->role ?? 'user',
                    str($note->content)->stripTags()->limit(200),
                    $note->created_at->format('Y-m-d H:i'),
                );
            }
        }

        return $output;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'quest_id' => $schema->integer()->required(),
        ];
    }
}
