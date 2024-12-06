<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\School;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\School\VocabularyTest as VocabularyTestModel;

class VocabularyTest extends HolocronComponent
{
    public $blurred = true;

    public VocabularyTestModel $test;

    public function render()
    {
        $word = $this->test->leftWords()->count() ? $this->test->leftWords()->random() : null;

        return view('holocron.school.vocabulary-test', [
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
