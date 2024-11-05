<?php

use App\Http\Controllers\Holocron\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/holocron/dashboard', DashboardController::class)->name('holocron.dashboard');
