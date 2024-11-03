<?php

use Illuminate\Support\Facades\Route;

Route::name('holocron.')->prefix('holocron')->group(function () {
    Route::get('login', [App\Http\Controllers\Holocron\LoginController::class, 'show'])->name('login-form');
    Route::post('login', [App\Http\Controllers\Holocron\LoginController::class, 'create'])->name('login');

    Route::group(['middleware' => 'auth'], function () {
        Route::get('/', App\Http\Controllers\Holocron\DashboardController::class)->name('dashboard');

        require __DIR__.'/school.php';
    });
});
