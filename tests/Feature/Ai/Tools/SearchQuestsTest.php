<?php

declare(strict_types=1);

use App\Ai\Tools\SearchQuests;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;

it('returns matching quests from search', function () {
    config(['scout.driver' => 'collection']);

    Quest::factory()->create(['name' => 'Buy groceries', 'description' => 'Milk and eggs']);
    Quest::factory()->create(['name' => 'Clean house', 'description' => 'Vacuum and mop']);

    $tool = new SearchQuests;
    $result = $tool->handle(new Request(['query' => 'groceries']));

    expect($result)
        ->toContain('Buy groceries')
        ->toContain('Milk and eggs');
});

it('returns no quests found message when no results match', function () {
    config(['scout.driver' => 'collection']);

    $tool = new SearchQuests;
    $result = $tool->handle(new Request(['query' => 'nonexistent']));

    expect($result)->toBe('No quests found matching the query.');
});

it('formats results with id, name, description, completed status, and date', function () {
    config(['scout.driver' => 'collection']);

    $quest = Quest::factory()->create([
        'name' => 'Test Quest',
        'description' => 'Test description',
        'date' => '2026-03-01',
        'completed_at' => null,
    ]);

    $tool = new SearchQuests;
    $result = $tool->handle(new Request(['query' => 'Test']));

    expect($result)
        ->toContain("ID: {$quest->id}")
        ->toContain('Name: Test Quest')
        ->toContain('Test description')
        ->toContain('Completed: No')
        ->toContain('Date: 2026-03-01');
});

it('defines a schema with query and limit parameters', function () {
    $tool = new SearchQuests;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('query')
        ->toHaveKey('limit')
        ->and($schema['query'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['limit'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class);
});
