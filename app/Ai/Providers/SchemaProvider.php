<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use Illuminate\Support\Facades\Schema;

class SchemaProvider
{
    /** @var list<string> */
    private const array TABLE_PREFIXES = [
        'grind_',
        'quest',
        'daily_goals',
        'agent_conversation',
        'chopper_directives',
        'users',
        'user_settings',
    ];

    public function generate(): string
    {
        $tables = $this->getRelevantTables();
        $output = "## Database Schema\n\n";

        foreach ($tables as $table) {
            $columns = Schema::getColumnListing($table);
            $columnList = implode(', ', $columns);
            $output .= "### {$table}\n{$columnList}\n\n";
        }

        return $output;
    }

    /**
     * @return list<string>
     */
    private function getRelevantTables(): array
    {
        $allTables = Schema::getTables();
        $relevant = [];

        foreach ($allTables as $table) {
            $name = $table['name'];

            foreach (self::TABLE_PREFIXES as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $relevant[] = $name;
                    break;
                }
            }
        }

        sort($relevant);

        return $relevant;
    }
}
