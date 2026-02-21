<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Holocron\_Shared\Livewire\Chopper;
use Modules\Holocron\_Shared\Livewire\Scrobbles;
use Modules\Holocron\Dashboard\Livewire\Index;

Route::middleware(['web', 'auth'])->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/', fn () => redirect()->route('holocron.dashboard'));
    Route::livewire('/dashboard', Index::class)->name('dashboard');
    Route::livewire('/chopper/{conversationId?}', Chopper::class)->name('chopper');
    Route::livewire('/scrobbles', Scrobbles::class)->name('scrobbles');
});
