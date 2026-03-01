<?php

declare(strict_types=1);

use App\Ai\Tools\ListQuests;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Quest\Models\Quest;

it('lists open quests by default', function () {
    Quest::factory()->create(['name' => 'Open Quest', 'is_note' => false, 'completed_at' => null]);
    Quest::factory()->create(['name' => 'Completed Quest', 'is_note' => false, 'completed_at' => now()]);

    $tool = new ListQuests;
    $result = $tool->handle(new Request([]));

    expect($result)
        ->toContain('Open Quest')
        ->not->toContain('Completed Quest');
});

it('filters completed quests', function () {
    Quest::factory()->create(['name' => 'Open Quest', 'is_note' => false, 'completed_at' => null]);
    Quest::factory()->create(['name' => 'Done Quest', 'is_note' => false, 'completed_at' => now()]);

    $tool = new ListQuests;
    $result = $tool->handle(new Request(['filter' => 'completed']));

    expect($result)
        ->toContain('Done Quest')
        ->not->toContain('Open Quest');
});

it('filters today quests', function () {
    Quest::factory()->create(['name' => 'Today Quest', 'is_note' => false, 'date' => today(), 'daily' => false, 'completed_at' => null]);
    Quest::factory()->create(['name' => 'Future Quest', 'is_note' => false, 'date' => today()->addWeek(), 'daily' => false, 'completed_at' => null]);

    $tool = new ListQuests;
    $result = $tool->handle(new Request(['filter' => 'today']));

    expect($result)
        ->toContain('Today Quest')
        ->not->toContain('Future Quest');
});

it('filters daily quests', function () {
    Quest::factory()->create(['name' => 'Daily Quest', 'is_note' => false, 'daily' => true, 'completed_at' => null]);
    Quest::factory()->create(['name' => 'Normal Quest', 'is_note' => false, 'daily' => false, 'completed_at' => null]);

    $tool = new ListQuests;
    $result = $tool->handle(new Request(['filter' => 'daily']));

    expect($result)
        ->toContain('Daily Quest')
        ->not->toContain('Normal Quest');
});

it('filters notes', function () {
    Quest::factory()->create(['name' => 'A Note', 'is_note' => true]);
    Quest::factory()->create(['name' => 'A Quest', 'is_note' => false]);

    $tool = new ListQuests;
    $result = $tool->handle(new Request(['filter' => 'notes']));

    expect($result)
        ->toContain('A Note')
        ->not->toContain('A Quest');
});

it('lists all quests excluding notes', function () {
    Quest::factory()->create(['name' => 'Quest One', 'is_note' => false]);
    Quest::factory()->create(['name' => 'Note One', 'is_note' => true]);

    $tool = new ListQuests;
    $result = $tool->handle(new Request(['filter' => 'all']));

    expect($result)
        ->toContain('Quest One')
        ->not->toContain('Note One');
});

it('returns no quests found message when empty', function () {
    $tool = new ListQuests;
    $result = $tool->handle(new Request(['filter' => 'open']));

    expect($result)->toBe("No quests found with filter 'open'.");
});

it('respects the limit parameter', function () {
    Quest::factory()->count(5)->create(['is_note' => false, 'completed_at' => null]);

    $tool = new ListQuests;
    $result = $tool->handle(new Request(['filter' => 'open', 'limit' => 2]));

    $lines = array_filter(explode("\n", $result));
    expect($lines)->toHaveCount(2);
});

it('falls back to open quests for an unknown filter value', function () {
    Quest::factory()->create(['name' => 'Open Quest', 'is_note' => false, 'completed_at' => null]);
    Quest::factory()->create(['name' => 'Completed Quest', 'is_note' => false, 'completed_at' => now()]);

    $tool = new ListQuests;
    $result = $tool->handle(new Request(['filter' => 'nonexistent_filter']));

    expect($result)
        ->toContain('Open Quest')
        ->not->toContain('Completed Quest');
});

it('defines a schema with filter and limit parameters', function () {
    $tool = new ListQuests;
    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('filter')
        ->toHaveKey('limit')
        ->and($schema['filter'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class)
        ->and($schema['limit'])->toBeInstanceOf(Illuminate\JsonSchema\Types\IntegerType::class);
});
