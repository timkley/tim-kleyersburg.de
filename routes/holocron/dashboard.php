<?php

declare(strict_types=1);

use App\Livewire\Holocron\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/', fn () => redirect()->route('holocron.dashboard'));
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});
