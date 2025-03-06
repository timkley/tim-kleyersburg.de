<?php

declare(strict_types=1);

namespace App\Livewire\Articles;

use App\Models\Article;
use BenBjurstrom\Prezet\Prezet;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        if (app()->environment('local')) {
            Prezet::updateIndex();
        }

        $articles = Article::published()
            ->simplePaginate(10);

        return view('articles.index', [
            'articles' => $articles,
            'paginator' => $articles,
        ]);
    }
}
