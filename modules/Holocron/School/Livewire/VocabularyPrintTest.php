<?php

declare(strict_types=1);

namespace Modules\Holocron\School\Livewire;

use Illuminate\View\View;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\School\Models\VocabularyTest as VocabularyTestModel;

class VocabularyPrintTest extends HolocronComponent
{
    public VocabularyTestModel $test;

    public string $mode = 'random';

    public function render(): View
    {
        return view('holocron-school::vocabulary-print-test', [
            'words' => $this->test->words(),
        ]);
    }
}
