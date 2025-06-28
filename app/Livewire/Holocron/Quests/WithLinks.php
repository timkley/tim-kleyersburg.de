<?php

declare(strict_types=1);

namespace App\Livewire\Holocron\Quests;

use App\Jobs\CrawlWebpageInformation;
use App\Models\Webpage;
use Livewire\Attributes\Validate;

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
}
