<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Modules\Holocron\Quest\Models\Note;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\SystemMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

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
        $this->streamedAnswer = '';

        $messages[] = new SystemMessage(view('prompts.solution')->render());
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

        $answer = Prism::text()
            ->using(Provider::OpenRouter, 'google/gemini-2.5-flash:online')
            ->withMessages([...$messages])
            ->asStream();

        foreach ($answer as $chunk) {
            $content = $chunk->text;

            if (empty($content)) {
                continue; // Skip empty chunks
            }
            $this->streamedAnswer .= $content;

            $this->stream(
                to: 'streamedAnswer',
                content: str($this->streamedAnswer)->markdown(),
                replace: true
            );
        }

        Note::find($noteId)->update([
            'content' => str($this->streamedAnswer)->markdown(),
        ]);
    }
}
