<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Bookmarks\Components;

use App\Jobs\CrawlWebpageInformation;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Bookmark as BookmarkModel;
use Flux;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;

class Bookmark extends HolocronComponent
{
    public BookmarkModel $bookmark;

    public string $url;

    public ?string $title;

    public ?string $description;

    public ?string $summary;

    public string $cleanUrl;

    public ?string $base64Favicon;

    public function mount(BookmarkModel $bookmark): void
    {
        $this->bookmark = $bookmark;
        $parsedUrl = parse_url($this->bookmark->webpage->url);
        $cleanUrl = mb_rtrim($parsedUrl['host'].($parsedUrl['path'] ?? ''), '/');

        $this->url = $bookmark->webpage->url;
        $this->title = $bookmark->webpage->title ?? $cleanUrl;
        $this->description = $bookmark->webpage->description;
        $this->summary = $bookmark->webpage->summary;
        $this->cleanUrl = $cleanUrl;
        $this->base64Favicon = $bookmark->webpage->favicon ? 'data:image/x-icon;base64,'.base64_encode($bookmark->webpage->favicon) : null;
    }

    public function render(): View
    {
        return view('holocron.bookmarks.components.bookmark', []);
    }

    #[Renderless]
    public function recrawl(): void
    {
        CrawlWebpageInformation::dispatch($this->bookmark->webpage);

        Flux::toast('Lesezeichen wird neu gecrawlt.');
    }

    public function updated($property, $value): void
    {
        $this->bookmark->update([
            $property => $value,
        ]);
    }
}
