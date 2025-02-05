<?php

declare(strict_types=1);

use App\Livewire\Holocron\Login;
use Illuminate\Support\Facades\Route;

Route::name('holocron.')->prefix('holocron')->group(function () {
    Route::get('login', Login::class)->name('login');
});
