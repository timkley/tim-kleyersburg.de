<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\School;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\School\VocabularyTest as VocabularyTestModel;
use Illuminate\View\View;

class VocabularyPrintTest extends HolocronComponent
{
    public VocabularyTestModel $test;

    public string $mode = 'random';

    public function render(): View
    {
        return view('holocron.school.vocabulary-print-test', [
            'words' => $this->test->words(),
        ]);
    }
}
