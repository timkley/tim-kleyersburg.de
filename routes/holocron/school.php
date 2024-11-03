<?php

use App\Http\Controllers\Holocron\SchoolController;
use Illuminate\Support\Facades\Route;

Route::get('school', SchoolController::class)->name('school');
