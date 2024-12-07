<?php

declare(strict_types=1);

namespace App\Livewire\Holocron;

use App\Jobs\Holocron\CrawlBookmarkInformation;
use App\Models\Holocron\Bookmark;
use Flux;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;

#[Title('Lesezeichen')]
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
    }

    public function recrawl(int $id): void
    {
        CrawlBookmarkInformation::dispatch(Bookmark::find($id));

        Flux::toast('Bookmark information is being recrawled.');
    }

    public function delete(int $id): void
    {
        Bookmark::find($id)->delete();
    }
}
