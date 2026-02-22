<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;
use Stringable;

class CreateQuest implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Primary intent: create a new quest or quest-note item. Invoke when user says: "Erstelle eine neue Quest ...", "Lege Aufgabe an ...", or asks to create a note task entry. Do not invoke when: the user wants to update, complete, or comment on an existing quest.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $quest = Quest::create([
            'name' => $request['name'],
            'description' => $request['description'] ?? '',
            'date' => $request['date'] ?? null,
            'quest_id' => $request['parent_id'] ?? null,
            'is_note' => $request['is_note'] ?? false,
            'attachments' => [],
        ]);

        return sprintf('Quest created successfully. ID: %d, Name: %s', $quest->id, $quest->name);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->required(),
            'description' => $schema->string(),
            'date' => $schema->string(),
            'parent_id' => $schema->integer(),
            'is_note' => $schema->boolean(),
        ];
    }
}
