<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Holocron\School\Livewire\Information;
use Modules\Holocron\School\Livewire\Vocabulary;
use Modules\Holocron\School\Livewire\VocabularyPrintTest;
use Modules\Holocron\School\Livewire\VocabularyTest;

Route::middleware(['web', 'auth'])->name('holocron.school.')->prefix('holocron/school')->group(function () {
    Route::get('/information', Information::class)->name('information');
    Route::get('/vocabulary', Vocabulary::class)->name('vocabulary.overview');
    Route::get('/vocabulary/test/{test}', VocabularyTest::class)->name('vocabulary.test');
    Route::get('/vocabulary/print-test/{test}', VocabularyPrintTest::class)->name('vocabulary.print-test');
});
