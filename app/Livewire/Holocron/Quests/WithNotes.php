<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Models\Holocron\Quest\Note;
use Livewire\Attributes\Validate;

trait WithNotes
{
    #[Validate('required')]
    #[Validate('min:3')]
    public string $noteDraft = '';

    public function addNote(): void
    {
        $this->validateOnly('noteDraft');

        $this->quest->notes()->create([
            'content' => $this->noteDraft,
        ]);

        $this->reset(['noteDraft']);
    }

    public function deleteNote(int $id): void
    {
        Note::destroy($id);
    }
}
