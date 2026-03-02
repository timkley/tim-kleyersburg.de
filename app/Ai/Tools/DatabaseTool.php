<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

class DatabaseTool implements Tool
{
    /** @var list<string> */
    private const array ALLOWED_STATEMENTS = ['select', 'insert', 'update', 'show', 'describe', 'explain', 'pragma'];

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Execute SQL queries against the database. Supports SELECT, INSERT, UPDATE, SHOW, DESCRIBE, EXPLAIN, PRAGMA. Returns JSON for reads, affected row count for writes. Schema summary is in your system prompt — use DESCRIBE/PRAGMA for full details.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = mb_trim($request['query']);
        $statementType = $this->parseStatementType($query);

        if (! in_array($statementType, self::ALLOWED_STATEMENTS, true)) {
            return "Statement type '{$statementType}' is not allowed. Only SELECT, INSERT, UPDATE, SHOW, DESCRIBE, EXPLAIN, and PRAGMA are permitted.";
        }

        try {
            return match ($statementType) {
                'insert' => $this->executeInsert($query),
                'update' => $this->executeUpdate($query),
                default => $this->executeSelect($query),
            };
        } catch (Throwable $e) {
            return "Query error: {$e->getMessage()}";
        }
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->required(),
        ];
    }

    private function parseStatementType(string $query): string
    {
        return mb_strtolower(strtok($query, " \t\n\r") ?: '');
    }

    private function executeSelect(string $query): string
    {
        $results = DB::select($query);

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    private function executeInsert(string $query): string
    {
        DB::insert($query);

        return 'Insert successful.';
    }

    private function executeUpdate(string $query): string
    {
        $affected = DB::update($query);

        return "Update successful. {$affected} row(s) affected.";
    }
}
