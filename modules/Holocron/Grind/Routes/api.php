<?php

declare(strict_types=1);

use App\Http\Middleware\BearerToken;
use Illuminate\Support\Facades\Route;
use Modules\Holocron\Grind\Controller\SyncHealthDataController;

Route::middleware(BearerToken::class)
    ->name('holocron.grind.')
    ->prefix('api/holocron/grind')
    ->group(function () {
        Route::post('/health', SyncHealthDataController::class)->name('health.sync');
    });
