<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use Denk\Facades\Denk;
use Denk\ValueObjects\DeveloperMessage;
use Denk\ValueObjects\UserMessage;

trait WithAI
{
    public function generateSolution(): void
    {
        $prompt = <<<'EOT'
Aufgabenstruktur:
---

EOT;

        foreach ($this->quest->breadcrumb() as $index => $quest) {
            $indent = str_repeat('  ', $index);

            $prompt .= <<<EOT
{$indent}- Name: {$quest->name}
{$indent}  Beschreibung: {$quest->description}

EOT;
        }

        $prompt .= '---';

        $solution = Denk::text()
            ->model('google/gemini-2.5-flash-preview-05-20:online')
            ->messages([
                new DeveloperMessage(view('prompts.solution')->render()),
                new UserMessage($prompt),
            ])
            ->generate();

        $this->quest->notes()->create([
            'content' => str($solution)->markdown(),
        ]);
    }

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
