<?php

declare(strict_types=1);

namespace Modules\Holocron\School;

use Illuminate\Support\ServiceProvider;
use Modules\Holocron\School\Services\Untis;

class SchoolServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Untis::class, fn (): Untis => new Untis(
            server: 'hektor',
            school: 'JoergR',
            username: config('services.untis.user'),
            password: config('services.untis.password')
        ));
    }

    public function boot(): void
    {
        // Load migrations for this module
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');

        // Load views and assign namespace 'order'
        $this->loadViewsFrom(__DIR__.'/Views', 'holocron-school');
    }
}
