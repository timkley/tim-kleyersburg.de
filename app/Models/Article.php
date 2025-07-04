<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Prezet\Prezet\Models\Document;
use Prezet\Prezet\Prezet;
use Spatie\Feed\FeedItem;

class Article
{
    /**
     * @return Document
     */
    public static function find(string $slug)
    {
        return Document::query()
            ->where('slug', $slug)
            ->when(config('app.env') !== 'local', fn ($query) => $query->where('draft', false))
            ->firstOrFail();
    }

    /**
     * @return Builder<Document>
     */
    public static function published(): Builder
    {
        return Document::when(fn ($query): bool => config('app.env') !== 'local', fn ($query) => $query->where('draft', false))
            ->orderBy('date', 'desc');
    }

    /**
     * @return EloquentCollection<int, Document>
     */
    public static function related(Document $document): EloquentCollection
    {
        return self::published()
            ->where('slug', '!=', $document->slug)
            ->whereHas('tags', function ($query) use ($document): void {
                $query->whereIn('tag_id', $document->tags->pluck('id'));
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    /**
     * @return Collection<int, FeedItem>
     */
    public static function getAllFeedItems(): Collection
    {
        return self::published()
            ->get()
            ->map(fn (Document $document): FeedItem => FeedItem::create([
                'id' => $document->slug,
                'title' => $document->frontmatter->title,
                'summary' => Prezet::parseMarkdown(Prezet::getMarkdown($document->filepath))->getContent(),
                'updated' => $document->frontmatter->date,
                'link' => route('prezet.show', $document->slug),
                'authorName' => 'Tim Kleyersburg',
            ]));
    }
}
