<?php

declare(strict_types=1);

use App\Ai\Tools\CompleteQuest;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;

it('completes an open quest', function () {
    $quest = Quest::factory()->create(['name' => 'Finish report']);

    $tool = new CompleteQuest;
    $result = $tool->handle(new Request(['quest_id' => $quest->id]));

    expect($result)
        ->toContain('has been marked as completed')
        ->toContain('Finish report')
        ->toContain("ID: {$quest->id}");

    $quest->refresh();
    expect($quest->isCompleted())->toBeTrue();
});

it('returns already completed message for completed quest', function () {
    $quest = Quest::factory()->create([
        'name' => 'Old task',
        'completed_at' => now(),
    ]);

    $tool = new CompleteQuest;
    $result = $tool->handle(new Request(['quest_id' => $quest->id]));

    expect($result)->toBe("Quest 'Old task' is already completed.");
});

it('returns not found message for non-existent quest', function () {
    $tool = new CompleteQuest;
    $result = $tool->handle(new Request(['quest_id' => 99999]));

    expect($result)->toBe('Quest with ID 99999 not found.');
});

it('defines a schema with quest_id parameter', function () {
    $tool = new CompleteQuest;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('quest_id')
        ->and($schema['quest_id'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class);
});
