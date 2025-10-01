<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Holocron\Grind\Livewire;

Route::middleware(['web', 'auth'])->name('holocron.')->prefix('holocron/grind')->group(function () {
    Route::get('/', fn () => to_route('holocron.grind.workouts.index'))->name('grind');
    Route::get('/exercises', Livewire\Exercises\Index::class)->name('grind.exercises.index');
    Route::get('/exercises/{exercise}', Livewire\Exercises\Show::class)->name('grind.exercises.show');
    Route::get('/plans', Livewire\Plans\Index::class)->name('grind.plans.index');
    Route::get('/plans/{plan}', Livewire\Plans\Show::class)->name('grind.plans.show');
    Route::get('/workouts', Livewire\Workouts\Index::class)->name('grind.workouts.index');
    Route::get('/workouts/{workout}', Livewire\Workouts\Show::class)->name('grind.workouts.show');

    Route::get('/health-data', Livewire\HealthData::class)->name('grind.health-data');
});
