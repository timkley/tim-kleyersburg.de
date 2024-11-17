<?php

namespace App\Livewire\Holocron\School\Components;

use App\Models\VocabularyWord as VocabularyWordModel;
use Livewire\Component;

class VocabularyWord extends Component
{
    public VocabularyWordModel $word;

    public string $german = '';

    public string $english = '';

    public function mount(VocabularyWordModel $word)
    {
        $this->word = $word;

        $this->german = $word->german;
        $this->english = $word->english;
    }

    public function render()
    {
        return view('holocron.school.components.vocabulary-word');
    }

    public function updated($property, $value): void
    {
        $this->word->update([
            $property => $value,
        ]);
    }
}
