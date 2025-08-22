<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Denk\Facades\Denk;
use Denk\ValueObjects\UserMessage;

trait WithAI
{
    public function generateSubquests(): void
    {
        $children = $this->quest->children->implode('name', '\n');

        $this->subquestSuggestions = Denk::json()
            ->model('google/gemini-flash-1.5-8b')
            ->properties([
                'subtasks' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'The name of the subtask',
                            ],
                        ],
                    ],
                ],
            ])
            ->messages([
                new UserMessage(view('prompts.subquests', [
                    'name' => $this->name,
                    'description' => $this->description,
                    'children' => $children,
                ])->render()),
            ])->generate()['subtasks'];
    }
}
