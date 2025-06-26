<?php

declare(strict_types=1);

namespace App\Livewire\Articles;

use App\Models\Article;
use Denk\Facades\Denk;
use Denk\ValueObjects\DeveloperMessage;
use Denk\ValueObjects\UserMessage;
use Illuminate\View\View;
use Livewire\Component;
use Prezet\Prezet\Models\Document;
use Prezet\Prezet\Prezet;

class Show extends Component
{
    public Document $article;

    public bool $rambling = false;

    public function mount(string $slug): void
    {
        $this->article = Article::find($slug);
    }

    public function render(): View
    {
        $document = Prezet::getDocumentDataFromFile($this->article->filepath);
        $frontmatter = $document->frontmatter;
        $markdown = Prezet::getMarkdown($this->article->filepath);

        if ($this->rambling) {
            $markdown = $this->rambleIt($markdown);
        }

        $content = Prezet::parseMarkdown($markdown);

        $related = Article::related($this->article);

        return view('articles.show', [
            'document' => $document,
            'frontmatter' => $frontmatter,
            'content' => $content,
            'headings' => Prezet::getHeadings($content->getContent()),
            'minutesToRead' => ceil(str($content->getContent())->stripTags()->wordCount() / 250).' minutes',
            'related' => $related,
        ]);
    }

    public function ramble(): void
    {
        $this->rambling = ! $this->rambling;
    }

    private function rambleIt(string $original): string
    {
        return cache()->store('file_persistent')->rememberForever('ramble.'.$this->article->id, function () use ($original) {
            $messages = [
                new DeveloperMessage(
                    <<<'EOT'
- only return markdown
- don't include any code highlighting backticks
- keep the heading structure of the article
- make sure that frontmatter is valid, values must be enclosed in double quotes if they are of type string
EOT
                ),
                new UserMessage(
                    <<<EOT
Rewrite the following blog post from the perspective of a frustrated, 35-year-old who’s fed up with the cluelessness around them. This person’s got no patience left for people who just can’t get a handle on the simplest things, keep burning up everyone’s time, and never make a single change to improve their sloppy workflows. Instead, they endlessly complain about how overwhelmed they are. As they type, they get progressively angrier, using more intense language, slurs, and visceral imagery—basically writing themselves into a bottomless rage. Expect the f-word to make increasingly frequent appearances as they rant. The core message should be:  _“Stop wasting my and your fucking time.”_

```
$original
```
EOT
                ),
            ];

            return Denk::text()->model('google/gemini-2.5-flash-preview')->messages($messages)->generate();
        });
    }
}
