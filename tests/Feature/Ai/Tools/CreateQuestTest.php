<?php

declare(strict_types=1);

use App\Ai\Tools\CreateQuest;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;

it('creates a quest with required name only', function () {
    $tool = new CreateQuest;
    $result = $tool->handle(new Request(['name' => 'New Quest']));

    expect($result)
        ->toContain('Quest created successfully')
        ->toContain('New Quest');

    $this->assertDatabaseHas('quests', [
        'name' => 'New Quest',
        'description' => '',
    ]);
});

it('creates a quest with all optional fields', function () {
    $parent = Quest::factory()->create();

    $tool = new CreateQuest;
    $result = $tool->handle(new Request([
        'name' => 'Sub Quest',
        'description' => 'Detailed description',
        'date' => '2026-03-01',
        'parent_id' => $parent->id,
        'is_note' => true,
    ]));

    expect($result)
        ->toContain('Quest created successfully')
        ->toContain('Sub Quest');

    $this->assertDatabaseHas('quests', [
        'name' => 'Sub Quest',
        'description' => 'Detailed description',
        'date' => '2026-03-01',
        'quest_id' => $parent->id,
        'is_note' => true,
    ]);
});

it('creates a quest with defaults for missing optional fields', function () {
    $tool = new CreateQuest;
    $tool->handle(new Request(['name' => 'Minimal Quest']));

    $quest = Quest::query()->where('name', 'Minimal Quest')->first();

    expect($quest)
        ->description->toBe('')
        ->date->toBeNull()
        ->quest_id->toBeNull()
        ->is_note->toBeFalse();
});

it('defines a schema with name, description, date, parent_id, and is_note parameters', function () {
    $tool = new CreateQuest;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('name')
        ->toHaveKey('description')
        ->toHaveKey('date')
        ->toHaveKey('parent_id')
        ->toHaveKey('is_note')
        ->and($schema['name'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['description'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['date'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['parent_id'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class)
        ->and($schema['is_note'])->toBeInstanceOf(Illuminate\JsonSchema\Types\BooleanType::class);
});
