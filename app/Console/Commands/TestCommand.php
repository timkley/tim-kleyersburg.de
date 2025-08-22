<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Holocron\School\Jobs\CheckForNewThings;

class TestCommand extends Command
{
    protected $signature = 't';

    public function handle(): void
    {
        CheckForNewThings::dispatch();
    }
}
