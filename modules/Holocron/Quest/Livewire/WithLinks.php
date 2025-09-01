<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Livewire;

use Livewire\Attributes\Validate;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;

trait WithLinks
{
    #[Validate('required')]
    #[Validate('url')]
    public string $linkDraft = '';

    public function addLink(): void
    {
        $this->validateOnly('linkDraft');

        $webpage = Webpage::createOrFirst([
            'url' => $this->linkDraft,
        ]);

        if ($webpage->wasRecentlyCreated) {
            CrawlWebpageInformation::dispatch($webpage);
        }

        $this->quest->webpages()->attach($webpage);

        $this->reset(['linkDraft']);
    }

    public function deleteLink(int $pivotId): void
    {
        $this->quest->webpages()->wherePivot('id', $pivotId)->detach();
    }
}
