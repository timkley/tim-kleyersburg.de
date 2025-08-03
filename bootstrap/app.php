<?php

declare(strict_types=1);

use App\Http\Middleware\BearerToken;
use App\Jobs\Holocron\Health\AwardExperience;
use App\Jobs\Holocron\Health\CreateDailyGoals;
use App\Jobs\Holocron\School\CheckForNewThings;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/public/pages.php',
            __DIR__.'/../routes/public/articles.php',
            __DIR__.'/../routes/holocron/auth.php',
            __DIR__.'/../routes/holocron/pages.php',
            __DIR__.'/../routes/holocron/helpers.php',
            __DIR__.'/../routes/holocron/school.php',
            __DIR__.'/../routes/holocron/gear.php',
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => route('holocron.login'));
        $middleware->api([BearerToken::class]);
    })
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->job(AwardExperience::class)->dailyAt('23:55');
        $schedule->job(CreateDailyGoals::class)->dailyAt('00:01');
        $schedule->job(CheckForNewThings::class)->hourly()->between('7:00', '18:00');
        $schedule->command('reminders:process')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
