<?php

declare(strict_types=1);

namespace App\Ai\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DirectivesProvider
{
    public function generate(): string
    {
        if (! Schema::hasTable('chopper_directives')) {
            return '';
        }

        $directives = DB::select('SELECT content FROM chopper_directives WHERE deactivated_at IS NULL ORDER BY id');

        if (empty($directives)) {
            return '';
        }

        $output = "## Deine gelernten Regeln\n\n";
        $output .= "Die folgenden Regeln hast du dir gemerkt. Befolge sie immer:\n";

        foreach ($directives as $directive) {
            $output .= "- {$directive->content}\n";
        }

        return $output;
    }
}
