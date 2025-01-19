<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Bookmarks\Components;

use App\Jobs\Holocron\CrawlBookmarkInformation;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Bookmark as BookmarkModel;
use Flux;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;

class Bookmark extends HolocronComponent
{
    public BookmarkModel $bookmark;

    public ?string $title;

    public ?string $description;

    public string $cleanUrl;

    public ?string $base64Favicon;

    public function mount(BookmarkModel $bookmark): void
    {
        $this->bookmark = $bookmark;
        $parsedUrl = parse_url($this->bookmark->url);
        $cleanUrl = mb_rtrim($parsedUrl['host'].($parsedUrl['path'] ?? ''), '/');

        $this->title = $bookmark->title ?? $cleanUrl;
        $this->description = $bookmark->description;
        $this->cleanUrl = $cleanUrl;
        $this->base64Favicon = $bookmark->favicon ? 'data:image/x-icon;base64,'.base64_encode($bookmark->favicon) : null;
    }

    public function render(): View
    {
        return view('holocron.bookmarks.components.bookmark', []);
    }

    #[Renderless]
    public function recrawl(): void
    {
        CrawlBookmarkInformation::dispatch($this->bookmark);

        Flux::toast('Lesezeichen wird neu gecrawlt.');
    }

    public function updated($property, $value): void
    {
        $this->bookmark->update([
            $property => $value,
        ]);
    }
}
