<?php

declare(strict_types=1);

use App\Ai\Agents\ChopperAgent;
use App\Ai\Tools\AddNoteToQuest;
use App\Ai\Tools\BrowseNotes;
use App\Ai\Tools\CompleteQuest;
use App\Ai\Tools\CreateQuest;
use App\Ai\Tools\GetQuest;
use App\Ai\Tools\ListQuests;
use App\Ai\Tools\LogMeal;
use App\Ai\Tools\QueryNutrition;
use App\Ai\Tools\ReadNote;
use App\Ai\Tools\SearchNotes;
use App\Ai\Tools\SearchQuestComments;
use App\Ai\Tools\SearchQuests;
use App\Ai\Tools\WriteNote;
use Laravel\Ai\Contracts\Tool;

it('defines name-first quest routing anchors in chopper instructions', function () {
    $instructions = (string) (new ChopperAgent)->instructions();

    expect($instructions)->toContain(
        'Nutzer nennen Quests fast immer ueber Namen, nicht ueber IDs.',
        'Bei Quest-Namen zuerst SearchQuests oder ListQuests nutzen.',
        'ID-basierte Quest-Tools (GetQuest, CompleteQuest, AddNoteToQuest) erst nach Aufloesung verwenden.',
        'Wenn mehrere Quests passen, frage nach, bevor du eine schreibende Aktion ausfuehrst.',
        'Nutze maximal ein Tool pro Anfrage, ausser die Quest-Aufloesung braucht den zweiten Schritt.'
    );
});

it('provides invocation and anti-invocation anchors for every tool description', function () {
    foreach (chopperTools() as $tool) {
        $description = (string) $tool->description();

        expect($description)->toContain('Primary intent:');
        expect($description)->toContain('Invoke when user says:');
        expect($description)->toContain('Do not invoke when:');
    }
});

it('marks id-based quest tools as post-resolution only', function () {
    foreach ([new GetQuest, new CompleteQuest, new AddNoteToQuest] as $tool) {
        $description = (string) $tool->description();

        expect($description)->toContain('after resolving by name with SearchQuests or ListQuests');
    }
});

it('keeps primary intent markers unique across all tools', function () {
    $intents = collect(chopperTools())
        ->map(fn (Tool $tool): string => (string) $tool->description())
        ->map(function (string $description): string {
            preg_match('/Primary intent:\s*(.+?)\./', $description, $matches);

            return $matches[1] ?? '';
        })
        ->all();

    expect($intents)->not->toContain('');
    expect(array_unique($intents))->toHaveCount(count($intents));
});

/**
 * @return array<int, Tool>
 */
function chopperTools(): array
{
    return [
        new SearchQuests,
        new SearchQuestComments,
        new ListQuests,
        new GetQuest,
        new CreateQuest,
        new CompleteQuest,
        new AddNoteToQuest,
        new BrowseNotes,
        new ReadNote,
        new WriteNote,
        new SearchNotes,
        new LogMeal,
        new QueryNutrition,
    ];
}
