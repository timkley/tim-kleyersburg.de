<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\School\Components;

use App\Models\Holocron\School\VocabularyWord as VocabularyWordModel;
use Illuminate\View\View;
use Livewire\Component;

class VocabularyWord extends Component
{
    public VocabularyWordModel $word;

    public string $german = '';

    public string $english = '';

    public function mount(VocabularyWordModel $word): void
    {
        $this->word = $word;

        $this->german = $word->german;
        $this->english = $word->english;
    }

    public function render(): View
    {
        return view('holocron.school.components.vocabulary-word');
    }

    public function updated(string $property, mixed $value): void
    {
        $this->word->update([
            $property => $value,
        ]);
    }
}
