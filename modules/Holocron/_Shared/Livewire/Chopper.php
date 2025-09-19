<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

#[Title('Chopper')]
class Chopper extends HolocronComponent
{
    public string $question = '';

    public string $answer = '';

    public string $context = '';

    public function ask(): void
    {
        $context = Quest::search($this->question)->options([
            'query_by' => 'embedding',
            'prefix' => false,
            'drop_tokens_threshold' => 0,
            'per_page' => 5,
        ])
            ->get()
            ->take(5)
            ->map(fn (Quest $quest) => $quest->name.': '.$quest->description)
            ->implode(', ');

        $context .= Note::search($this->question)->options([
            'query_by' => 'embedding',
            'prefix' => false,
            'drop_tokens_treshold' => 0,
            'per_page' => 5,
        ])
            ->get()
            ->map(fn (Note $note) => $note->content)
            ->implode(', ');

        $response = Prism::text()
            ->using(Provider::OpenRouter, 'google/gemini-2.5-flash')
            ->withSystemPrompt("You are an assistant for question-answering. You can only make conversations based on the provided context. If a response cannot be formed strictly using the context, politely say you don't have knowledge about that topic.")
            ->withPrompt(<<<EOT
<Context>
    $context
</Context>

<Question>
    $this->question
</Question>
EOT
            )
            ->asStream();

        foreach ($response as $chunk) {
            $content = $chunk->text;

            if (empty($content)) {
                continue; // Skip empty chunks
            }
            $this->answer .= $content;

            $this->stream(
                to: 'answer',
                content: str($this->answer)->markdown(),
                replace: true
            );
        }

        $this->context = $context;
    }

    public function render(): View
    {
        return view('holocron::chopper');
    }
}
