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
        return 'Primary intent: add a comment to an existing resolved quest. Invoke when user says: "Fuege Notiz zu Quest ... hinzu" or after resolving by name with SearchQuests or ListQuests. Do not invoke when: the user wants to create or edit a markdown knowledge-base note.';
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
