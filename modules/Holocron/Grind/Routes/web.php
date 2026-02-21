<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Holocron\Grind\Livewire;

Route::middleware(['web', 'auth'])->name('holocron.')->prefix('holocron/grind')->group(function () {
    Route::get('/', fn () => to_route('holocron.grind.workouts.index'))->name('grind');
    Route::livewire('/exercises', Livewire\Exercises\Index::class)->name('grind.exercises.index');
    Route::livewire('/exercises/{exercise}', Livewire\Exercises\Show::class)->name('grind.exercises.show');
    Route::livewire('/plans', Livewire\Plans\Index::class)->name('grind.plans.index');
    Route::livewire('/plans/{plan}', Livewire\Plans\Show::class)->name('grind.plans.show');
    Route::livewire('/workouts', Livewire\Workouts\Index::class)->name('grind.workouts.index');
    Route::livewire('/workouts/{workout}', Livewire\Workouts\Show::class)->name('grind.workouts.show');

    Route::livewire('/nutrition', Livewire\Nutrition\Index::class)->name('grind.nutrition.index');
    Route::livewire('/body-measurements', Livewire\Nutrition\BodyMeasurements::class)->name('grind.body-measurements');
    Route::livewire('/health-data', Livewire\HealthData::class)->name('grind.health-data');
});
