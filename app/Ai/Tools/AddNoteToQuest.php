<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Actions\CreateNote;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class AddNoteToQuest implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Add a note/comment to an existing quest. Useful for adding context, updates, or information to a task.';
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

        $note = (new CreateNote)->handle($quest, [
            'content' => $request['content'],
        ]);

        return sprintf("Note added to quest '%s' (ID: %d). Note ID: %d", $quest->name, $quest->id, $note->id);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'quest_id' => $schema->integer()->required(),
            'content' => $schema->string()->required(),
        ];
    }
}
