<?php

use App\Livewire\Holocron\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});
