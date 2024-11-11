<?php

use App\Livewire\Pages\Einmaleins;
use App\Livewire\Pages\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class);
Route::get('/einmaleins', Einmaleins::class);
