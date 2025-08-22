<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/bookmarks', Modules\Holocron\Bookmarks\Livewire\Index::class)->name('bookmarks');
});
