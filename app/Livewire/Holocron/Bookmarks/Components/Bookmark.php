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

    public function mount(BookmarkModel $bookmark): void
    {
        $this->bookmark = $bookmark;

        $this->title = $bookmark->title;
    }

    public function render(): View
    {
        return view('holocron.bookmarks.components.bookmark', [ ]);
    }

    #[Renderless]
    public function recrawl(): void
    {
        CrawlBookmarkInformation::dispatch($this->bookmark);

        Flux::toast('Lesezeichen wird neu gecrawlt.');
    }
}
