<?php

declare(strict_types=1);

namespace Modules\Holocron\School\Livewire;

use Illuminate\View\View;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\School\Models\VocabularyTest as VocabularyTestModel;

class VocabularyTest extends HolocronComponent
{
    public VocabularyTestModel $test;

    public bool $blurred = true;

    public string $mode = 'random';

    public function render(): View
    {
        $word = $this->test->leftWords()->count() !== 0 ? $this->test->leftWords()->random() : null;

        return view('holocron-school::vocabulary-test', [
            'word' => $word,
            'finished' => $this->test->finished,
        ]);
    }

    public function markAsCorrect(int $wordId): void
    {
        $this->blurred = true;
        $this->test->markAsCorrect($wordId);
    }

    public function markAsWrong(int $wordId): void
    {
        $this->blurred = true;
        $this->test->markAsWrong($wordId);
        $this->test->increment('error_count');
    }
}
