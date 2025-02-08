<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\School;

use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\School\VocabularyTest;
use App\Models\Holocron\School\VocabularyWord;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;

class Vocabulary extends HolocronComponent
{
    use WithPagination;

    #[Rule('required', 'string')]
    public string $german = '';

    #[Rule('required', 'string')]
    public string $english = '';

    public string $filter = 'all';

    public function render(): View
    {
        $words = $this->filteredWords()->paginate(10);
        $tests = VocabularyTest::limit(10)->latest()->get();

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
        if ($this->filteredWords()->count() === 0) {
            return;
        }

        $vocabularyTest = VocabularyTest::create([
            'word_ids' => $this->filteredWords()->get()->pluck('id')->toArray(),
        ]);

        return $this->redirect(route('holocron.school.vocabulary.test', [$vocabularyTest->id]));
    }

    public function startRandomTest()
    {
        $wordIds = VocabularyWord::inRandomOrder()->limit(50)->pluck('id')->toArray();

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

    private function filteredWords()
    {
        return VocabularyWord::when($this->filter === 'low_score', fn ($query) => $query->whereRaw('`right` - `wrong` < ?', [3]))
            ->when($this->filter === 'middle_score', fn ($query) => $query->whereRaw('`right` - `wrong` < ?', [5]))
            ->when($this->filter === 'high_score', fn ($query) => $query->whereRaw('`right` - `wrong` >= ?', [3]))
            ->latest();
    }
}
