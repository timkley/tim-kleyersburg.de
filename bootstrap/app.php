<?php

declare(strict_types=1);

use App\Jobs\Holocron\Health\CheckSufficientCreatineIntake;
use App\Jobs\Holocron\Health\CheckSufficientWaterIntake;
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
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (Request $request) => route('holocron.login'));
    })
    ->withSchedule(function (Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->job(CheckForNewThings::class)->hourly()->between('7:00', '18:00');
        $schedule->job(CheckSufficientWaterIntake::class)->hourly()->between('8:00', '20:00');
        $schedule->job(CheckSufficientCreatineIntake::class)->everyTwoHours()->between('10:00', '21:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
