<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Holocron\Quest\Livewire;

Route::middleware(['web', 'auth'])->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/quests', Livewire\Index::class)->name('quests');
    Route::get('/quests/{quest}', Livewire\Show::class)->name('quests.show');
});
