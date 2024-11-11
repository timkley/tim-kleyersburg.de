<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use BenBjurstrom\Prezet\Models\Document;
use BenBjurstrom\Prezet\Prezet;
use Livewire\Component;
use OpenAI\Laravel\Facades\OpenAI;

class Show extends Component
{
    public Document $article;

    public $rambling = false;

    public function mount(string $slug)
    {
        $this->article = Article::find($slug);
    }

    public function render()
    {
        $frontmatter = Prezet::getFrontmatter($this->article->filepath);
        $markdown = Prezet::getMarkdown($this->article->filepath);

        if ($this->rambling) {
            $markdown = $this->rambleIt($markdown);
        }

        $content = Prezet::getContent($markdown);

        $related = Article::related($this->article);

        return view('articles.show', [
            'frontmatter' => $frontmatter,
            'content' => $content,
            'minutesToRead' => ceil(str($content)->stripTags()->wordCount() / 250).' minutes',
            'related' => $related->pluck('frontmatter'),
        ]);
    }

    public function ramble(): void
    {
        $this->rambling = ! $this->rambling;
    }

    private function rambleIt(string $original)
    {
        return cache()->rememberForever('ramble.'.$this->article->id, function () use ($original) {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => <<<EOT
Rewrite the following blog post from the perspective of a frustrated, 35-year-old who’s fed up with the cluelessness around them. This person’s got no patience left for people who just can’t get a handle on the simplest things, keep burning up everyone’s time, and never make a single change to improve their sloppy workflows. Instead, they endlessly complain about how overwhelmed they are. As they type, they get progressively angrier, using more intense language, slurs, and visceral imagery—basically writing themselves into a bottomless rage. Expect the f-word to make increasingly frequent appearances as they rant. The core message should be:  _“Stop wasting my and your fucking time.”_

```
$original
```
EOT
                    ],
                ],
            ]);

            return data_get($response, 'choices.0.message.content');
        });
    }
}
