<?php

declare(strict_types=1);

namespace App\Jobs\Holocron;

use App\Models\Holocron\Bookmark;
use Denk\Facades\Denk;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class CrawlBookmarkInformation implements ShouldQueue
{
    use Queueable;

    public function __construct(public Bookmark $bookmark)
    {
    }

    public function handle(): void
    {
        $url = $this->bookmark->url;
        $parsedUrl = parse_url($url);

        $faviconResponse = Http::get($parsedUrl['scheme'].'://'.$parsedUrl['host'].'/favicon.ico');
        $favicon = $faviconResponse->ok() ? $faviconResponse->body() : null;
        $crawl = Http::withToken(config('services.firecrawl.api_key'))->post('https://firecrawl.wacg.dev/v1/scrape', [
            'url' => $url,
        ])->json();

        $title = data_get($crawl, 'data.metadata.title');
        $description = data_get($crawl, 'data.metadata.description');
        $summary = $this->createSummary(data_get($crawl, 'data.markdown'));

        $this->bookmark->update([
            'favicon' => $favicon,
            'title' => $title,
            'description' => $description,
            'summary' => $summary,
        ]);
    }

    protected function createSummary(string $body): string
    {
        return Denk::text()
            ->prompt(<<<EOT
Summarize the the given webpage content in triple quotes in 1-2 sentences, focus on the purpose only. Exclude information like:
- login elements
- contact information
- cookie consent / data privacy
- footer information
- payment / upgrade information

If no content was provided answer with "No content provided."

"""
$body
"""
EOT
)
            ->generate();
    }
}
