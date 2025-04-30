<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Webpage;
use Denk\Facades\Denk;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class CrawlWebpageInformation implements ShouldQueue
{
    use Queueable;

    public function __construct(public Webpage $url) {}

    public function handle(): void
    {
        $url = $this->url->url;
        $parsedUrl = parse_url($url);

        $faviconResponse = Http::get($parsedUrl['scheme'].'://'.$parsedUrl['host'].'/favicon.ico');
        $favicon = $faviconResponse->ok() ? $faviconResponse->body() : null;
        $crawl = Http::withToken(config('services.firecrawl.api_key'))->post('https://firecrawl.wacg.dev/v1/scrape', [
            'url' => $url,
        ])->json();

        $title = data_get($crawl, 'data.metadata.title');
        $description = data_get($crawl, 'data.metadata.description');
        /** @phpstan-ignore-next-line  */
        $summary = $this->createSummary($description.' '.data_get($crawl, 'data.markdown') ?? data_get($crawl, 'data.rawHtml'));

        $this->url->update([
            'favicon' => $favicon,
            'title' => $title,
            'description' => $description,
            'summary' => $summary,
        ]);
    }

    protected function createSummary(string $content): string
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
$content
"""
EOT
            )
            ->generate();
    }
}
