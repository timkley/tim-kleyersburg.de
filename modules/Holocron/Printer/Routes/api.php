<?php

declare(strict_types=1);

use App\Http\Middleware\BearerToken;
use Illuminate\Support\Facades\Route;
use Modules\Holocron\Printer\Controller\PrintQueueController;
use Modules\Holocron\Printer\Controller\PrintSomethingController;

Route::middleware(BearerToken::class)->name('holocron.')->prefix('api/holocron')->group(function () {
    Route::post('/printer/print', PrintSomethingController::class)->name('api.printer.print');
    Route::get('/printer/queue', PrintQueueController::class)->name('api.printer.queue');
});
