<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\StringSchema;

trait WithAI
{
    public function generateSubquests(): void
    {
        $children = $this->quest->children->implode('name', '\n');

        $schema = new ArraySchema(
            name: 'subtasks',
            description: 'An array of subtasks',
            items: new StringSchema('subtask', 'A single subtask')
        );

        $response = Prism::structured()
            ->using(Provider::OpenRouter, 'google/gemini-2.0-flash-001')
            ->withSchema($schema)
            ->withPrompt(view('prompts.subquests', [
                'name' => $this->name,
                'description' => $this->description,
                'children' => $children,
            ]))
            ->asStructured();

        $this->subquestSuggestions = $response->structured;
    }
}
