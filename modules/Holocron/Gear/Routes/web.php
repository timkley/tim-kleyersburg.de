<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Holocron\Gear\Livewire;

Route::middleware(['web', 'auth'])->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/gear', Livewire\Index::class)->name('gear');
    Route::get('/gear/categories', Livewire\Categories\Index::class)->name('gear.categories');
    Route::get('/gear/items', Livewire\Items\Index::class)->name('gear.items');
    Route::get('/gear/journeys/{journey}', Livewire\Journeys\Show::class)->name('gear.journeys.show');
});
