<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Holocron\Quest\Controller\CompleteController;
use Modules\Holocron\Quest\Livewire;

Route::get('/holocron/quests/complete', CompleteController::class)
    ->name('holocron.quests.complete')
    ->middleware('signed');

Route::middleware(['web', 'auth'])->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/quests', Livewire\Index::class)->name('quests');
    Route::get('/quests/daily', Livewire\DailyQuest::class)->name('quests.daily');
    Route::get('/quests/recurring', Livewire\RecurringQuests::class)->name('quests.recurring');
    Route::get('/quests/{quest}', Livewire\Show::class)->name('quests.show');
});
