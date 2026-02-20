<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Actions;

use Illuminate\Support\Facades\Validator;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Models\Quest;

final readonly class AddQuestLink
{
    public function handle(Quest $quest, array $data): Quest
    {
        $validated = Validator::make($data, [
            'url' => ['required', 'url'],
            'title' => ['nullable', 'string'],
        ])->validate();

        $webpage = Webpage::createOrFirst(['url' => $validated['url']]);

        if ($webpage->wasRecentlyCreated) {
            CrawlWebpageInformation::dispatch($webpage);
        }

        $quest->webpages()->attach($webpage, [
            'title' => $validated['title'] ?? null,
        ]);

        return $quest->refresh();
    }
}
