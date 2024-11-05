<?php

use Illuminate\Support\Facades\Route;

Route::name('holocron.')->prefix('holocron')->group(function () {
    Route::get('login', [App\Http\Controllers\Holocron\LoginController::class, 'show'])->name('login-form');
    Route::post('login', [App\Http\Controllers\Holocron\LoginController::class, 'create'])->name('login');
});
