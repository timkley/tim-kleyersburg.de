<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Models\Holocron\Quest\Note;
use Denk\Facades\Denk;
use Denk\ValueObjects\AssistantMessage;
use Denk\ValueObjects\DeveloperMessage;
use Denk\ValueObjects\UserMessage;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;

trait WithNotes
{
    #[Validate('required')]
    #[Validate('min:3')]
    public string $noteDraft = '';

    #[Url]
    public bool $chat = false;

    public string $streamedAnswer = '';

    public function addNote(): void
    {
        $this->validateOnly('noteDraft');

        $this->quest->notes()->create([
            'content' => $this->noteDraft,
        ]);

        $this->reset(['noteDraft']);

        if ($this->chat) {
            $note = $this->quest->notes()->create([
                'role' => 'assistant',
                'created_at' => now()->addSecond(),
            ]);

            $this->js('$wire.ask('.$note->id.')');
        }
    }

    public function deleteNote(int $id): void
    {
        Note::destroy($id);
    }

    public function ask(int $noteId): void
    {
        $messages[] = new DeveloperMessage(view('prompts.solution')->render());
        $prompt = <<<'EOT'
Aufgabenstruktur:
---

EOT;

        foreach ($this->quest->breadcrumb() as $index => $quest) {
            $indent = str_repeat('  ', $index);

            $prompt .= <<<EOT
{$indent}- Name: {$quest->name}
{$indent}  Beschreibung: {$quest->description}

EOT;
        }

        $prompt .= '---';
        $messages[] = new UserMessage($prompt);

        $this->quest->notes->each(function (Note $note) use (&$messages) {
            if (is_null($note->content)) {
                return;
            }
            $messages[] = $note->role === 'user' ?
                new UserMessage($note->content) :
                new AssistantMessage($note->content);
        });

        $answer = Denk::text()
            ->model('google/gemini-2.5-flash-preview-05-20:online')
            ->messages([
                new DeveloperMessage(view('prompts.solution')->render()),
                new UserMessage($prompt),
                ...$messages,
            ])
            ->generate();

        Note::find($noteId)->update([
            'content' => str($answer)->markdown(),
        ]);

        $this->dispatch('note-updated.'.$noteId);
    }
}
