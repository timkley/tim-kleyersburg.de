<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class CompleteQuest implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mark a quest as completed by its ID. This awards XP to the user.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $quest = Quest::find($request['quest_id']);

        if (! $quest) {
            return "Quest with ID {$request['quest_id']} not found.";
        }

        if ($quest->isCompleted()) {
            return "Quest '{$quest->name}' is already completed.";
        }

        $quest->complete();

        return sprintf("Quest '%s' (ID: %d) has been marked as completed.", $quest->name, $quest->id);
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
