<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Bookmarks;

use App\Jobs\Holocron\CrawlBookmarkInformation;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Bookmark;
use Flux;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

class Overview extends HolocronComponent
{
    use WithPagination;

    #[Validate(['required', 'url', 'unique:bookmarks'])]
    public string $url;

    public function render(): View
    {
        return view('holocron.bookmarks.bookmarks', [
            'bookmarks' => Bookmark::latest()->simplePaginate(20),
        ]);
    }

    public function submit(): void
    {
        $this->validate();

        $bookmark = Bookmark::create([
            'url' => $this->url,
        ]);

        CrawlBookmarkInformation::dispatch($bookmark);

        $this->reset('url');

        Flux::toast('Lesezeichen wurde hinzugefügt.');
    }

    public function delete(int $id): void
    {
        Bookmark::find($id)->delete();

        Flux::toast('Lesezeichen gelöscht.');
    }
}
