<?php

declare(strict_types=1);

namespace App\Models;

use BenBjurstrom\Prezet\Models\Document;
use BenBjurstrom\Prezet\Prezet;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Feed\FeedItem;

class Article
{
    public static function find(string $slug)
    {
        return Document::query()
            ->where('slug', $slug)
            ->when(config('app.env') !== 'local', function ($query) {
                return $query->where('draft', false);
            })
            ->firstOrFail();
    }

    public static function published()
    {
        return Document::when(fn ($query) => config('app.env') !== 'local', function ($query) {
            return $query->where('draft', false);
        })
            ->orderBy('date', 'desc');
    }

    public static function related(Document $document): Collection
    {
        return self::published()
            ->where('slug', '!=', $document->slug)
            ->whereHas('tags', function ($query) use ($document) {
                $query->whereIn('tag_id', $document->tags->pluck('id'));
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    public static function getAllFeedItems()
    {
        return self::published()
            ->get()
            ->map(fn (Document $document) => FeedItem::create([
                'id' => $document->slug,
                'title' => $document->frontmatter->title,
                'summary' => Prezet::getContent(Prezet::getMarkdown($document->filepath)),
                'updated' => $document->created_at,
                'link' => route('prezet.show', $document->slug),
                'authorName' => 'Tim Kleyersburg',
            ]));
    }
}
