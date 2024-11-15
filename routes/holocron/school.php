<?php

use App\Livewire\Holocron\School\Overview;
use App\Livewire\Holocron\School\Vocabulary;
use App\Livewire\Holocron\School\VocabularyTest;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.school.')->prefix('holocron/school')->group(function () {
    Route::get('/', Overview::class)->name('index');

    Route::get('/vocabulary', Vocabulary::class)->name('vocabulary.overview');
    Route::get('/vocabulary/test/{test}', VocabularyTest::class)->name('vocabulary.test');
});
