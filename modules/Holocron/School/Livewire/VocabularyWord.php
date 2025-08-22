<?php

declare(strict_types=1);

namespace Modules\Holocron\School\Livewire;

use Illuminate\View\View;
use Livewire\Component;
use Modules\Holocron\School\Models\VocabularyWord as VocabularyWordModel;

class VocabularyWord extends Component
{
    public VocabularyWordModel $word;

    public string $german = '';

    public string $english = '';

    public function updated(string $property, mixed $value): void
    {
        $this->word->update([
            $property => $value,
        ]);
    }

    public function mount(VocabularyWordModel $word): void
    {
        $this->word = $word;

        $this->german = $word->german;
        $this->english = $word->english;
    }

    public function render(): View
    {
        return view('holocron-school::vocabulary-word');
    }
}
