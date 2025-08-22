<?php

declare(strict_types=1);

namespace Modules\Holocron\Bookmarks;

use Illuminate\Support\ServiceProvider;

class BookmarksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load migrations for this module
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');

        // Load views and assign namespace 'order'
        $this->loadViewsFrom(__DIR__.'/Views', 'holocron-bookmarks');
    }
}
