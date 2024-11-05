<?php

use App\Http\Controllers\Holocron\SchoolController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.school.')->prefix('holocron/school')->group(function () {
    Route::get('/', SchoolController::class)->name('index');
});
