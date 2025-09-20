<?php

declare(strict_types=1);

use App\Http\Middleware\BearerToken;
use Illuminate\Support\Facades\Route;
use Modules\Holocron\Quest\Controller\CreateQuestController;
use Modules\Holocron\Quest\Controller\PrintQueueController;

Route::middleware(BearerToken::class)->name('holocron.')->prefix('api/holocron')->group(function () {
    Route::post('/quests/create', CreateQuestController::class)->name('quests.create');
    Route::get('/quests/print-queue', PrintQueueController::class)->name('quests.print-queue');
});
