<?php

declare(strict_types=1);

use App\Livewire\Articles\Index;
use App\Livewire\Articles\Show;
use BenBjurstrom\Prezet\Http\Controllers\ImageController;
use BenBjurstrom\Prezet\Http\Controllers\OgimageController;
use Illuminate\Support\Facades\Route;

Route::get('/articles', Index::class)->name('articles.index');

Route::get('/articles/img/{path}', ImageController::class)
    ->name('prezet.image')
    ->where('path', '.*');

Route::get('/articles/ogimage/{slug}', OgimageController::class)
    ->name('prezet.ogimage')
    ->where('slug', '.*');

Route::get('/articles/{slug}', Show::class)
    ->where('slug', '.*')
    ->name('prezet.show');

Route::feeds();
