<?php

use Illuminate\Support\Facades\Route;

Route::get('/', App\Http\Controllers\HomeController::class);
Route::get('/einmaleins', App\Http\Controllers\EinmaleinsController::class);
