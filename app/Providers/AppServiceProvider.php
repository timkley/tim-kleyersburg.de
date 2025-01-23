<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Services\Untis;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
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
        $this->configureDates();
        $this->configureModels();
        $this->configureGates();
    }

    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    private function configureModels(): void
    {
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());
        Model::shouldBeStrict();
        Model::unguard();
    }

    private function configureGates(): void
    {
        Gate::define('viewPulse', function (?User $user) {
            return auth()->user()?->isTim() ? Response::allow() : redirect()->route('holocron.login');
        });

        Gate::define('isTim', function (?User $user) {
            return auth()->user()?->isTim() ? Response::allow() : redirect()->route('holocron.login');
        });
    }
}
