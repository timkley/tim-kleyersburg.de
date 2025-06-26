<?php

declare(strict_types=1);

namespace App\Livewire\Articles;

use App\Models\Article;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Prezet\Prezet\Prezet;

class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        if (app()->environment('local')) {
            Prezet::updateIndex();
        }

        $articles = Article::published()
            ->paginate(10);

        return view('articles.index', [
            'articles' => $articles,
            'paginator' => $articles,
        ]);
    }
}
