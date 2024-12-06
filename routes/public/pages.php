<?php

declare(strict_types=1);

use App\Livewire\Pages\Einmaleins;
use App\Livewire\Pages\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('pages.home');
Route::get('/einmaleins', Einmaleins::class)->name('pages.einmaleins');
