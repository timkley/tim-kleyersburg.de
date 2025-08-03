<?php

declare(strict_types=1);

use App\Http\Controllers\Gear\CategoryController;
use App\Http\Controllers\Gear\ItemController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('gear')->name('gear.')->group(function () {
    Route::resource('categories', CategoryController::class)->except(['show', 'edit', 'create']);
    Route::resource('items', ItemController::class)->except(['show', 'edit', 'create']);
});
