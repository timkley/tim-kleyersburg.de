<?php

namespace App\Http\Controllers;

use App\Models\Article;
use BenBjurstrom\Prezet\Actions\UpdateIndex;
use BenBjurstrom\Prezet\Prezet;

class ArticlesController extends Controller
{
    public function index()
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

    public function show(string $slug)
    {
        $article = Article::find($slug);

        $frontmatter = Prezet::getFrontmatter($article->filepath);
        $content = Prezet::getContent(Prezet::getMarkdown($article->filepath));

        $related = Article::related($article);

        return view('articles.show', [
            'frontmatter' => $frontmatter,
            'content' => $content,
            'minutesToRead' => ceil(str($content)->stripTags()->wordCount() / 250).' minutes',
            'related' => $related->pluck('frontmatter'),
        ]);
    }
}
