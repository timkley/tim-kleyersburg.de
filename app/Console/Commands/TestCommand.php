<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Holocron\School\CheckForNewThings;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 't';

    public function handle()
    {
        CheckForNewThings::dispatch();
    }
}
