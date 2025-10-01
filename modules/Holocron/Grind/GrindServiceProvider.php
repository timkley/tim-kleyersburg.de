<?php

declare(strict_types=1);

namespace Modules\Holocron\Grind;

use Illuminate\Support\ServiceProvider;
use Modules\Holocron\Grind\Commands\ImportWorkouts;

class GrindServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load migrations for this module
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');

        // Load views and assign namespace 'order'
        $this->loadViewsFrom(__DIR__.'/Views', 'holocron-grind');

        // Load commands
        $this->commands([
            ImportWorkouts::class,
        ]);
    }
}
