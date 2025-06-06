<?php

declare(strict_types=1);

use App\Livewire\Holocron\Bookmarks;
use App\Livewire\Holocron\Dashboard;
use App\Livewire\Holocron\Experience;
use App\Livewire\Holocron\Grind;
use App\Livewire\Holocron\Quests;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.')->prefix('holocron')->group(function () {
    Route::get('/', fn () => redirect()->route('holocron.dashboard'));
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/bookmarks', Bookmarks\Overview::class)->name('bookmarks');

    Route::get('/quests', Quests\Overview::class)->name('quests');
    Route::get('/quests/{quest}', Quests\Show::class)->name('quests.show');

    Route::get('/grind', fn () => to_route('holocron.grind.workouts.index'))->name('grind');
    Route::get('/grind/exercises', Grind\Exercises\Index::class)->name('grind.exercises.index');
    Route::get('/grind/exercises/{exercise}', Grind\Exercises\Show::class)->name('grind.exercises.show');
    Route::get('/grind/plans', Grind\Plans\Index::class)->name('grind.plans.index');
    Route::get('/grind/plans/{plan}', Grind\Plans\Show::class)->name('grind.plans.show');
    Route::get('/grind/workouts', Grind\Workouts\Index::class)->name('grind.workouts.index');
    Route::get('/grind/workouts/{workout}', Grind\Workouts\Show::class)->name('grind.workouts.show');

    Route::get('/experience', Experience::class)->name('experience');
});
