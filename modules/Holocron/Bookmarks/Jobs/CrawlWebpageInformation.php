<?php

declare(strict_types=1);

namespace Modules\Holocron\Bookmarks\Jobs;

use Dom\HTMLDocument;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class CrawlWebpageInformation implements ShouldQueue
{
    use Queueable;

    public function __construct(public Webpage $webpage) {}

    public function handle(): void
    {
        $url = $this->webpage->url;
        $parsedUrl = parse_url($url);

        if (! $parsedUrl) {
            return;
        }

        $body = Http::get($url)->body();
        libxml_use_internal_errors(true);
        $document = HTMLDocument::createFromString($body);
        libxml_clear_errors();
        $title = $document->title;
        $description = $document->querySelector('meta[name="description"]')?->getAttribute('content');
        $content = $document->body->textContent;

        $summary = $this->createSummary($description.' '.$content);

        $this->webpage->update([
            'title' => $title,
            'description' => $description,
            'summary' => $summary,
        ]);
    }

    protected function createSummary(string $content): string
    {
        // Truncate content to avoid exceeding token limits (~4 chars per token, limit to ~100k tokens)
        $content = mb_substr($content, 0, 400_000);

        $response = Prism::text()
            ->using(Provider::OpenRouter, 'google/gemini-2.0-flash-001')
            ->withPrompt(<<<EOT
Summarize the given webpage content in 1-2 sentences, focus on the purpose only. Exclude information like:
- login elements
- contact information
- cookie consent / data privacy
- footer information
- payment / upgrade information

If no content was provided answer with "The summary could not be generated."

You will answer ONLY with the summary, no quotes, delimiters.

"""
$content
"""
EOT)
            ->asText();

        return $response->text;
    }
}
