<?php

use App\Livewire\Holocron\School\Information;
use App\Livewire\Holocron\School\Vocabulary;
use App\Livewire\Holocron\School\VocabularyTest;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->name('holocron.school.')->prefix('holocron/school')->group(function () {
    Route::get('/information', Information::class)->name('information');

    Route::get('/vocabulary', Vocabulary::class)->name('vocabulary.overview');
    Route::get('/vocabulary/test/{test}', VocabularyTest::class)->name('vocabulary.test');
});
