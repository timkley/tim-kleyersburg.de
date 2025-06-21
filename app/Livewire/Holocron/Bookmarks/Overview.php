<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Bookmarks;

use App\Jobs\CrawlWebpageInformation;
use App\Livewire\Holocron\HolocronComponent;
use App\Models\Holocron\Bookmark;
use App\Models\Webpage;
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
        return view('holocron.bookmarks.overview', [
            'bookmarks' => Bookmark::with('webpage')->latest()->simplePaginate(20),
        ]);
    }

    public function submit(): void
    {
        $this->validate();

        $webpage = Webpage::create([
            'url' => $this->url,
        ]);
        CrawlWebpageInformation::dispatch($webpage);

        Bookmark::create([
            'webpage_id' => $webpage->id,
        ]);

        $this->reset('url');

        Flux::toast('Lesezeichen wurde hinzugefügt.');
    }

    public function delete(int $id): void
    {
        Bookmark::find($id)->delete();

        Flux::toast('Lesezeichen gelöscht.');
    }
}
