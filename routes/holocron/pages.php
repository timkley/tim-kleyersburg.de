<?php

declare(strict_types=1);

use App\Livewire\Holocron\Bookmarks;
use App\Livewire\Holocron\Chopper;
use App\Livewire\Holocron\Dashboard\Index;
use App\Livewire\Holocron\Experience;
use App\Livewire\Holocron\Gear;
use App\Livewire\Holocron\Grind;
use App\Livewire\Holocron\Quests;
use App\Livewire\Holocron\Settings;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/', fn () => redirect()->route('holocron.dashboard'));
    Route::get('/dashboard', Index::class)->name('dashboard');

    Route::get('/settings', Settings::class)->name('settings');

    Route::get('/bookmarks', Bookmarks\Index::class)->name('bookmarks');

    Route::get('/quests', Quests\Index::class)->name('quests');
    Route::get('/quests/{quest}', Quests\Show::class)->name('quests.show');

    Route::get('/grind', fn () => to_route('holocron.grind.workouts.index'))->name('grind');
    Route::get('/grind/exercises', Grind\Exercises\Index::class)->name('grind.exercises.index');
    Route::get('/grind/exercises/{exercise}', Grind\Exercises\Show::class)->name('grind.exercises.show');
    Route::get('/grind/plans', Grind\Plans\Index::class)->name('grind.plans.index');
    Route::get('/grind/plans/{plan}', Grind\Plans\Show::class)->name('grind.plans.show');
    Route::get('/grind/workouts', Grind\Workouts\Index::class)->name('grind.workouts.index');
    Route::get('/grind/workouts/{workout}', Grind\Workouts\Show::class)->name('grind.workouts.show');

    Route::get('/gear', Gear\Index::class)->name('gear');
    Route::get('/gear/categories', Gear\Categories\Index::class)->name('gear.categories');
    Route::get('/gear/items', Gear\Items\Index::class)->name('gear.items');
    Route::get('/gear/journeys/{journey}', Gear\Journeys\Show::class)->name('gear.journeys.show');

    Route::get('/experience', Experience::class)->name('experience');

    Route::get('/chopper', Chopper::class)->name('chopper');
});
