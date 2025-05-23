<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\School;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\School\VocabularyTest as VocabularyTestModel;
use Illuminate\View\View;

class VocabularyTest extends HolocronComponent
{
    public VocabularyTestModel $test;

    public bool $blurred = true;

    public string $mode = 'random';

    public function render(): View
    {
        $word = $this->test->leftWords()->count() !== 0 ? $this->test->leftWords()->random() : null;

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
