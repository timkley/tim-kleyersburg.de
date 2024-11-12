<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Untis;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Untis::class, function () {
            return new Untis(
                server: 'hektor',
                school: 'JoergR',
                username: config('services.untis.user'),
                password: config('services.untis.password')
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function (?User $user) {
            return auth()->check() ? Response::allow() : redirect()->route('holocron.login');
        });
    }
}
