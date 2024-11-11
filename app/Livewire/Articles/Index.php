<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use BenBjurstrom\Prezet\Actions\UpdateIndex;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        if (app()->environment('local')) {
            UpdateIndex::handle();
        }

        $articles = Article::published()
            ->simplePaginate(10);

        return view('articles.index', [
            'articles' => $articles->pluck('frontmatter'),
            'paginator' => $articles,
        ]);
    }
}
