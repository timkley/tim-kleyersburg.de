<?php

declare(strict_types=1);

use App\Ai\Tools\AddNoteToQuest;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;

it('adds a note to an existing quest', function () {
    $quest = Quest::factory()->create(['name' => 'Buy groceries']);

    $tool = new AddNoteToQuest;
    $result = $tool->handle(new Request([
        'quest_id' => $quest->id,
        'content' => 'Remember to buy milk',
    ]));

    expect($result)
        ->toContain('Note added to quest')
        ->toContain('Buy groceries')
        ->toContain("ID: {$quest->id}");

    $this->assertDatabaseHas('quest_notes', [
        'quest_id' => $quest->id,
        'content' => 'Remember to buy milk',
    ]);
});

it('returns not found message for non-existent quest', function () {
    $tool = new AddNoteToQuest;
    $result = $tool->handle(new Request([
        'quest_id' => 99999,
        'content' => 'Some note',
    ]));

    expect($result)->toBe('Quest with ID 99999 not found.');
});

it('defines a schema with quest_id and content parameters', function () {
    $tool = new AddNoteToQuest;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('quest_id')
        ->toHaveKey('content')
        ->and($schema['quest_id'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class)
        ->and($schema['content'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class);
});
