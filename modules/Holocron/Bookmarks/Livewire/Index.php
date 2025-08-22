<?php

declare(strict_types=1);

namespace Modules\Holocron\Bookmarks\Livewire;

use Flux\Flux;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use Modules\Holocron\_Shared\Livewire\HolocronComponent;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Bookmark;
use Modules\Holocron\Bookmarks\Models\Webpage;

class Index extends HolocronComponent
{
    use WithPagination;

    #[Validate(['required', 'url', 'unique:bookmarks'])]
    public string $url;

    #[Url]
    public string $query = '';

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

    public function render(): View
    {
        if ($this->query) {
            $bookmarks = Bookmark::search($this->query)->paginate(20);
        } else {
            $bookmarks = Bookmark::with('webpage')->latest()->paginate(20);
        }

        return view('holocron-bookmarks::index', [
            'bookmarks' => $bookmarks,
        ]);
    }
}
