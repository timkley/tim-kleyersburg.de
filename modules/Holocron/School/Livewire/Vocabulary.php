<?php

declare(strict_types=1);

namespace Modules\Holocron\School\Livewire;

use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\School\Models\VocabularyTest;
use Modules\Holocron\School\Models\VocabularyWord;

class Vocabulary extends HolocronComponent
{
    use WithPagination;

    #[Rule('required', 'string')]
    public string $german = '';

    #[Rule('required', 'string')]
    public string $english = '';

    public function render(): View
    {
        $words = VocabularyWord::latest()->paginate(10);
        $tests = VocabularyTest::limit(10)->latest()->get();

        return view('holocron-school::vocabulary', ['words' => $words, 'tests' => $tests]);
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

    public function startTest(int $count = 50): null
    {
        $wordIds = VocabularyWord::orderByRaw('`right` - `wrong`')->limit($count)->pluck('id')->toArray();

        $vocabularyTest = VocabularyTest::create([
            'word_ids' => $wordIds,
        ]);

        return $this->redirect(route('holocron.school.vocabulary.test', [$vocabularyTest->id]));
    }

    public function deleteTest(int $id): void
    {
        if (! Gate::allows('isTim')) {
            abort(403);
        }

        VocabularyTest::find($id)->delete();
    }
}
