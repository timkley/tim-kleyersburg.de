<?php

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
            __DIR__.'/../routes/holocron/dashboard.php',
            __DIR__.'/../routes/holocron/helpers.php',
            __DIR__.'/../routes/holocron/school.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn (Request $request) => route('holocron.login'));
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->job(CheckForNewThings::class)->twiceDaily(7, 18);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();