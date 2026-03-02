<?php

declare(strict_types=1);

use App\Ai\Tools\DatabaseTool;
use Laravel\Ai\Tools\Request;

it('executes a SELECT query and returns JSON results', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request(['query' => 'SELECT 1 as value']));

    expect($result)->toContain('value');
});

it('executes an INSERT query and returns success', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => "INSERT INTO grind_nutrition_days (date, type, created_at, updated_at) VALUES ('2099-01-01', 'rest', datetime('now'), datetime('now'))",
    ]));

    expect($result)->toContain('Insert');
});

it('executes an UPDATE query and returns affected rows', function () {
    Modules\Holocron\Grind\Models\NutritionDay::factory()->create(['date' => '2099-02-01']);

    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => "UPDATE grind_nutrition_days SET type = 'training' WHERE date = '2099-02-01 00:00:00'",
    ]));

    expect($result)->toContain('1');
});

it('blocks DELETE queries', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => 'DELETE FROM grind_nutrition_days WHERE id = 1',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks DROP queries', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => 'DROP TABLE grind_nutrition_days',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks ALTER queries', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => 'ALTER TABLE grind_nutrition_days ADD COLUMN foo TEXT',
    ]));

    expect($result)->toContain('not allowed');
});

it('allows DESCRIBE queries', function () {
    $tool = new DatabaseTool;

    // SQLite uses PRAGMA instead of DESCRIBE, so we test with a PRAGMA
    $result = (string) $tool->handle(new Request([
        'query' => "PRAGMA table_info('grind_nutrition_days')",
    ]));

    expect($result)->toContain('date');
});

it('blocks INSERT into non-writable tables', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => "INSERT INTO users (email) VALUES ('hacked@test.com')",
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks UPDATE on non-writable tables', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => "UPDATE users SET password = 'hacked' WHERE 1=1",
    ]));

    expect($result)->toContain('not allowed');
});

it('allows INSERT into writable tables', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => "INSERT INTO chopper_directives (content, created_at, updated_at) VALUES ('test', datetime('now'), datetime('now'))",
    ]));

    expect($result)->toContain('Insert');
});

it('returns error for malformed SQL', function () {
    $tool = new DatabaseTool;

    $result = (string) $tool->handle(new Request([
        'query' => 'SELECT * FROM nonexistent_table_xyz',
    ]));

    expect($result)->toContain('Query error');
});

it('returns the expected schema definition', function () {
    $tool = new DatabaseTool;

    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('query')
        ->and($schema['query'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class);
});
