<?php

declare(strict_types=1);

namespace App\Providers;

use App\Data\Articles\CustomFrontmatterData;
use App\Models\User;
use App\Services\Untis;
use BenBjurstrom\Prezet\Data\FrontmatterData;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FrontmatterData::class, CustomFrontmatterData::class);

        $this->app->bind(Untis::class, fn (): Untis => new Untis(
            server: 'hektor',
            school: 'JoergR',
            username: config('services.untis.user'),
            password: config('services.untis.password')
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDates();
        $this->configureModels();
        $this->configureGates();
        $this->configureLivewire();
    }

    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    private function configureModels(): void
    {
        //        Model::shouldBeStrict();
        Model::unguard();
        Model::automaticallyEagerLoadRelationships();
    }

    private function configureGates(): void
    {
        Gate::define('viewPulse', fn (?User $user) => auth()->user()?->isTim() ? Response::allow() : redirect()->route('holocron.login'));

        Gate::define('isTim', fn (?User $user) => auth()->user()?->isTim() ? Response::allow() : redirect()->route('holocron.login'));
    }

    private function configureLivewire(): void
    {
        Livewire::useScriptTagAttributes(['id' => 'livewire-script']);
    }
}
