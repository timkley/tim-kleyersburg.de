<?php

namespace App\Livewire\Holocron\School;

use App\Models\VocabularyTest as VocabularyTestModel;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.holocron')]
class VocabularyTest extends Component
{
    public $blurred = true;

    use WithPagination;

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
