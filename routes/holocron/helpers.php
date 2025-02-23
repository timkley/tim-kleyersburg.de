<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.helpers.')->prefix('holocron/helpers')->group(function () {
    Route::get('discord-icon', function () {
        return view('holocron.helpers.discord-icon');
    })->name('discord-icon');
});
