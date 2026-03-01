<?php

declare(strict_types=1);

use App\Ai\Tools\GetQuest;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;

it('returns full details for an existing quest', function () {
    $quest = Quest::factory()->create([
        'name' => 'Important Quest',
        'description' => 'A detailed description',
        'date' => '2026-03-01',
        'daily' => false,
        'is_note' => false,
    ]);

    $tool = new GetQuest;
    $result = $tool->handle(new Request(['quest_id' => $quest->id]));

    expect($result)
        ->toContain("Quest ID: {$quest->id}")
        ->toContain('Name: Important Quest')
        ->toContain('A detailed description')
        ->toContain('Date: 2026-03-01')
        ->toContain('Completed: No')
        ->toContain('Is Note: No')
        ->toContain('Daily: No');
});

it('shows completed status for completed quest', function () {
    $quest = Quest::factory()->create([
        'name' => 'Done Quest',
        'completed_at' => now(),
    ]);

    $tool = new GetQuest;
    $result = $tool->handle(new Request(['quest_id' => $quest->id]));

    expect($result)->toContain('Completed: Yes');
});

it('includes sub-quests in output', function () {
    $parent = Quest::factory()->create(['name' => 'Parent Quest']);
    $child = Quest::factory()->create([
        'name' => 'Child Quest',
        'quest_id' => $parent->id,
    ]);

    $tool = new GetQuest;
    $result = $tool->handle(new Request(['quest_id' => $parent->id]));

    expect($result)
        ->toContain('Sub-quests:')
        ->toContain('Child Quest')
        ->toContain("ID: {$child->id}");
});

it('includes notes in output', function () {
    $quest = Quest::factory()->create(['name' => 'Quest with notes']);
    Note::factory()->for($quest)->create([
        'content' => 'This is a note',
        'role' => 'user',
    ]);

    $tool = new GetQuest;
    $result = $tool->handle(new Request(['quest_id' => $quest->id]));

    expect($result)
        ->toContain('Notes:')
        ->toContain('This is a note')
        ->toContain('[user]');
});

it('returns not found message for non-existent quest', function () {
    $tool = new GetQuest;
    $result = $tool->handle(new Request(['quest_id' => 99999]));

    expect($result)->toBe('Quest with ID 99999 not found.');
});

it('shows date as none when quest has no date', function () {
    $quest = Quest::factory()->create([
        'name' => 'No date quest',
        'date' => null,
    ]);

    $tool = new GetQuest;
    $result = $tool->handle(new Request(['quest_id' => $quest->id]));

    expect($result)->toContain('Date: none');
});

it('defines a schema with quest_id parameter', function () {
    $tool = new GetQuest;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('quest_id')
        ->and($schema['quest_id'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class);
});
