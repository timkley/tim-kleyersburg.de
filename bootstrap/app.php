<?php

declare(strict_types=1);

use App\Http\Middleware\BearerToken;
use App\Jobs\ArchiveScrobbles;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\Holocron\Quest\Jobs\ProcessReminders;
use Modules\Holocron\Quest\Jobs\RecurQuests;
use Modules\Holocron\School\Jobs\CheckForNewThings;
use Modules\Holocron\User\Jobs\AwardExperience;
use Modules\Holocron\User\Jobs\CreateDailyGoals;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
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
        $schedule->job(ProcessReminders::class)->everyMinute();
        $schedule->job(RecurQuests::class)->weekdays()->dailyAt('8:00');
        $schedule->job(RecurQuests::class)->weekends()->dailyAt('10:00');
        $schedule->job(ArchiveScrobbles::class)->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
