<?php

use Illuminate\Support\Facades\Route;

// public pages
Route::get('/', App\Http\Controllers\HomeController::class);
Route::get('/einmaleins', App\Http\Controllers\EinmaleinsController::class);
require __DIR__.'/public/articles.php';

// private pages
require __DIR__.'/holocron/holocron.php';
