<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer;

use Illuminate\Support\ServiceProvider;

class PrinterServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load migrations for this module
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');

        $this->loadViewsFrom(__DIR__.'/Views', 'holocron-printer');
    }
}
