<?php

declare(strict_types=1);

it('has broad german utterance coverage for tool routing', function () {
    $matrix = toolInvocationMatrix();

    expect(count($matrix))->toBeGreaterThanOrEqual(78);
});

it('contains valid case shape and known tool names', function () {
    $knownTools = knownTools();

    foreach (toolInvocationMatrix() as $index => $case) {
        expect($case)->toHaveKeys([
            'utterance',
            'expected_primary_tool',
            'allowed_secondary_tools',
            'must_not_call',
            'requires_disambiguation',
            'requires_name_resolution',
            'target_follow_up_tool',
        ]);

        expect($case['utterance'])->toBeString()->not->toBe('');
        expect($knownTools)->toContain($case['expected_primary_tool']);
        expect($case['allowed_secondary_tools'])->toBeArray();
        expect($case['must_not_call'])->toBeArray();
        expect($case['requires_disambiguation'])->toBeBool();
        expect($case['requires_name_resolution'])->toBeBool();

        if ($case['target_follow_up_tool'] !== null) {
            expect(in_array($case['target_follow_up_tool'], $knownTools, true))
                ->toBeTrue("Unknown target_follow_up_tool at matrix case {$index}");
        }

        foreach ($case['allowed_secondary_tools'] as $tool) {
            expect(in_array($tool, $knownTools, true))
                ->toBeTrue("Unknown secondary tool at matrix case {$index}");
        }

        foreach ($case['must_not_call'] as $tool) {
            expect(in_array($tool, $knownTools, true))
                ->toBeTrue("Unknown must_not_call tool at matrix case {$index}");
        }
    }
});

it('keeps utterances unique to avoid ambiguous routing contracts', function () {
    $utterances = array_column(toolInvocationMatrix(), 'utterance');

    expect(array_unique($utterances))->toHaveCount(count($utterances));
});

it('routes name-based quest intents through search first', function () {
    $cases = collect(toolInvocationMatrix())->where('requires_name_resolution', true);

    expect($cases)->not->toBeEmpty();

    foreach ($cases as $case) {
        expect($case['expected_primary_tool'])->toBe('SearchQuests');

        if ($case['target_follow_up_tool'] !== null && ! $case['requires_disambiguation']) {
            expect($case['allowed_secondary_tools'])->toContain($case['target_follow_up_tool']);
        }
    }
});

it('blocks direct write actions for ambiguous quest names', function () {
    $ambiguousCases = collect(toolInvocationMatrix())->where('requires_disambiguation', true);

    expect($ambiguousCases)->not->toBeEmpty();

    foreach ($ambiguousCases as $case) {
        expect($case['allowed_secondary_tools'])->toBe([]);
        expect($case['must_not_call'])->toContain('CompleteQuest');
        expect($case['must_not_call'])->toContain('AddNoteToQuest');
    }
});

it('covers every chopper tool at least six times across primary and secondary mappings', function () {
    $coverage = array_fill_keys(knownTools(), 0);

    foreach (toolInvocationMatrix() as $case) {
        $coverage[$case['expected_primary_tool']]++;

        foreach ($case['allowed_secondary_tools'] as $secondaryTool) {
            $coverage[$secondaryTool]++;
        }
    }

    foreach ($coverage as $tool => $count) {
        expect($count)->toBeGreaterThanOrEqual(6, "Coverage too low for {$tool}");
    }
});

/**
 * @return array<int, array{
 *   utterance: string,
 *   expected_primary_tool: string,
 *   allowed_secondary_tools: array<int, string>,
 *   must_not_call: array<int, string>,
 *   requires_disambiguation: bool,
 *   requires_name_resolution: bool,
 *   target_follow_up_tool: string|null
 * }>
 */
function toolInvocationMatrix(): array
{
    /** @var array<int, array{
     *   utterance: string,
     *   expected_primary_tool: string,
     *   allowed_secondary_tools: array<int, string>,
     *   must_not_call: array<int, string>,
     *   requires_disambiguation: bool,
     *   requires_name_resolution: bool,
     *   target_follow_up_tool: string|null
     * }> $matrix
     */
    $matrix = require base_path('tests/fixtures/chopper_tool_invocation_matrix.php');

    return $matrix;
}

/**
 * @return array<int, string>
 */
function knownTools(): array
{
    return [
        'SearchQuests',
        'SearchQuestComments',
        'ListQuests',
        'GetQuest',
        'CreateQuest',
        'CompleteQuest',
        'AddNoteToQuest',
        'BrowseNotes',
        'ReadNote',
        'WriteNote',
        'SearchNotes',
        'LogMeal',
        'EditMeal',
        'QueryNutrition',
    ];
}
