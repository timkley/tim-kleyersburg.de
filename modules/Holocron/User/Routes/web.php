<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Holocron\User\Livewire\Login;
use Modules\Holocron\User\Livewire\Settings;

Route::middleware('web')->name('holocron.')->prefix('holocron')->group(function () {
    Route::livewire('login', Login::class)->name('login');

    Route::middleware('auth')->group(function () {
        Route::livewire('/settings', Settings::class)->name('settings');
    });
});
