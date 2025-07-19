<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use App\Models\Holocron\Quest\Quest;
use Denk\Facades\Denk;
use Denk\ValueObjects\DeveloperMessage;
use Denk\ValueObjects\UserMessage;
use Illuminate\View\View;
use Livewire\Attributes\Title;

#[Title('Chopper')]
class Chopper extends HolocronComponent
{
    public string $question = '';

    public string $answer = '';

    public function ask(): void
    {
        $context = Quest::search($this->question)->options([
            'query_by' => 'name,description,embedding',
            'prefix' => false,
            'drop_tokens_threshold' => 0,
        ])
            ->get()
            ->map(fn (Quest $quest) => $quest->name.': '.$quest->description)
            ->implode(', ');

        $answer = Denk::text()
            ->model('google/gemini-2.5-flash')
            ->messages([
                new DeveloperMessage("You are an assistant for question-answering. You can only make conversations based on the provided context. If a response cannot be formed strictly using the context, politely say you don't have knowledge about that topic."),
                new UserMessage(<<<EOT
<Context>
    $context
</Context>

<Question>
    $this->question
</Question>
EOT
                ),
            ])->generateStreamed();

        foreach ($answer as $chunk) {
            $content = $chunk->choices[0]->delta->content;

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
    }

    public function render(): View
    {
        return view('holocron.chopper');
    }
}
