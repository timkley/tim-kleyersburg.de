<?php

namespace App\Livewire\Holocron\School;

use App\Models\VocabularyTest;
use App\Models\VocabularyWord;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.holocron')]
class Vocabulary extends Component
{
    use WithPagination;

    #[Rule('required', 'string')]
    public string $german = '';

    #[Rule('required', 'string')]
    public string $english = '';

    public $checkedWords;

    public function render()
    {
        $words = VocabularyWord::latest()->paginate(50);
        $tests = VocabularyTest::latest()->get();

        return view('holocron.school.vocabulary', compact('words', 'tests'));
    }

    public function addWord(): void
    {
        $this->validate();

        VocabularyWord::create([
            'german' => $this->german,
            'english' => $this->english,
        ]);

        $this->reset();
    }

    public function deleteWord(int $id): void
    {
        VocabularyWord::find($id)->delete();
    }

    public function startTest()
    {
        $vocabularyTest = VocabularyTest::create([
            'word_ids' => VocabularyWord::pluck('id')->toArray(),
        ]);

        return $this->redirect(route('holocron.school.vocabulary.test', [$vocabularyTest->id]));
    }
}
