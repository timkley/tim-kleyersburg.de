<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use App\Jobs\Holocron\CrawlBookmarkInformation;
use App\Models\Holocron\Bookmark;
use Flux;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

class Bookmarks extends HolocronComponent
{
    use WithPagination;

    #[Validate(['required', 'url', 'unique:bookmarks'])]
    public string $url;

    public function render(): View
    {
        return view('holocron.bookmarks', [
            'bookmarks' => Bookmark::simplePaginate(20),
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

    #[Renderless]
    public function recrawl(int $id): void
    {
        CrawlBookmarkInformation::dispatch(Bookmark::find($id));

        Flux::toast('Lesezeichen wird neu gecrawlt.');
    }

    public function delete(int $id): void
    {
        Bookmark::find($id)->delete();

        Flux::toast('Lesezeichen gelöscht.');
    }
}
