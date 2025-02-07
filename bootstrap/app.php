<?php

declare(strict_types=1);

use App\Http\Middleware\BearerToken;
use App\Jobs\Holocron\Health\CheckGoals;
use App\Jobs\Holocron\Health\CreateDailyGoals;
use App\Jobs\Holocron\School\CheckForNewThings;
use App\Jobs\Holocron\SendDailyDigest;
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
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (Request $request) => route('holocron.login'));
        $middleware->api([BearerToken::class]);
    })
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->job(CreateDailyGoals::class)->dailyAt('00:01');
        $schedule->job(CheckForNewThings::class)->hourly()->between('7:00', '18:00');
        $schedule->job(SendDailyDigest::class)->dailyAt('8:00');
        $schedule->job(CheckGoals::class)->cron('30 9,12,16,19 * * *');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
