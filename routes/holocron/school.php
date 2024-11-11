<?php

use App\Livewire\Holocron\School;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.school.')->prefix('holocron/school')->group(function () {
    Route::get('/', School::class)->name('index');
});
