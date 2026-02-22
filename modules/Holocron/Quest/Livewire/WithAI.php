<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Responses\StructuredAgentResponse;

use function Laravel\Ai\agent;

trait WithAI
{
    public function generateSubquests(): void
    {
        $children = $this->quest->children->implode('name', '\n');

        /** @var StructuredAgentResponse $response */
        $response = agent(schema: fn (JsonSchema $schema) => [
            'subtasks' => $schema->array()->items($schema->string()),
        ])->prompt((string) view('prompts.subquests', [
            'name' => $this->name,
            'description' => $this->description,
            'children' => $children,
        ]));

        $this->subquestSuggestions = $response->structured['subtasks'];
    }
}
