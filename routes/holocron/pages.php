<?php

declare(strict_types=1);

use App\Livewire\Holocron\Bookmarks\Bookmarks;
use App\Livewire\Holocron\Dashboard;
use App\Livewire\Holocron\Water;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/', fn () => redirect()->route('holocron.dashboard'));
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/bookmarks', Bookmarks::class)->name('bookmarks');
    Route::get('/water', Water::class)->name('water');
});
