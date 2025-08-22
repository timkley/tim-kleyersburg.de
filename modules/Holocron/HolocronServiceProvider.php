<?php

declare(strict_types=1);

namespace Modules\Holocron;

use Illuminate\Support\ServiceProvider;

class HolocronServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/_Shared/Views', 'holocron');
    }
}
