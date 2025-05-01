<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Webpage;
use Denk\Facades\Denk;
use Dom\HTMLDocument;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class CrawlWebpageInformation implements ShouldQueue
{
    use Queueable;

    public function __construct(public Webpage $webpage) {}

    public function handle(): void
    {
        $url = $this->webpage->url;
        $parsedUrl = parse_url($url);

        $faviconResponse = Http::get($parsedUrl['scheme'].'://'.$parsedUrl['host'].'/favicon.ico');
        $favicon = $faviconResponse->ok() ? $faviconResponse->body() : null;

        $body = Http::get($url)->body();
        libxml_use_internal_errors(true);
        $document = HTMLDocument::createFromString($body);
        libxml_clear_errors();
        $title = $document->title;
        $description = $document->querySelector('meta[name="description"]')?->getAttribute('content');
        $content = $document->body->textContent;

        $summary = $this->createSummary($description.' '.$content);

        $this->webpage->update([
            'favicon' => $favicon,
            'title' => $title,
            'description' => $description,
            'summary' => $summary,
        ]);
    }

    protected function createSummary(string $content): string
    {
        return Denk::text()
            ->model('google/gemini-flash-1.5-8b')
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
