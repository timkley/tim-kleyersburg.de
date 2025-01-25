<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.helpers.')->prefix('holocron/helpers')->group(function () {
    Route::get('discord-icon', function () {
        return view('holocron.helpers.discord-icon');
    })->name('discord-icon');

    Route::get('/csrf', fn () => response()->json(['csrf_token' => csrf_token()]))->withoutMiddleware([VerifyCsrfToken::class])
        ->name('csrf');
});
