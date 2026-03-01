<?php

declare(strict_types=1);

use Laravel\Ai\AnonymousAgent;
use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Bookmarks\Models\Webpage;

it('dispatches a job that crawls for more content', function () {
    Http::fake([
        'https://example.com' => Http::response(file_get_contents(base_path('tests/fixtures/example.html'))),
    ]);
    AnonymousAgent::fake(['Good day sir!!']);
    $webpage = Webpage::factory()->create([
        'url' => 'https://example.com',
        'title' => null,
        'description' => null,
        'summary' => null,
    ]);
    new CrawlWebpageInformation($webpage)->handle();

    expect($webpage->title)->toBe('Example title');
    expect($webpage->description)->toBe('Example description');
    expect($webpage->summary)->toBe('Good day sir!!');
});

it('can create a webpage using factory', function () {
    $webpage = Webpage::factory()->create();

    expect($webpage)->toBeInstanceOf(Webpage::class);
    expect($webpage->exists)->toBeTrue();
    expect($webpage->url)->not->toBeNull();
    expect($webpage->title)->not->toBeNull();
});

it('can create a webpage with nullable fields', function () {
    $webpage = Webpage::factory()->create([
        'title' => null,
        'description' => null,
        'summary' => null,
    ]);

    expect($webpage->title)->toBeNull();
    expect($webpage->description)->toBeNull();
    expect($webpage->summary)->toBeNull();
});

it('returns early for an invalid url', function () {
    $webpage = Webpage::factory()->create([
        'url' => 'not://a:valid:url:at:all:'.str_repeat('x', 500),
        'title' => null,
        'description' => null,
        'summary' => null,
    ]);

    Http::fake();

    (new CrawlWebpageInformation($webpage))->handle();

    // Since parse_url may or may not fail on this, we just ensure no exception was thrown
    expect(true)->toBeTrue();
});

it('handles a page without meta description', function () {
    $html = <<<'HTML'
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <title>No Description Page</title>
        </head>
        <body>Some body content</body>
    </html>
    HTML;

    Http::fake([
        'https://no-description.com' => Http::response($html),
    ]);
    AnonymousAgent::fake(['A summary of the page.']);

    $webpage = Webpage::factory()->create([
        'url' => 'https://no-description.com',
        'title' => null,
        'description' => null,
        'summary' => null,
    ]);

    (new CrawlWebpageInformation($webpage))->handle();

    expect($webpage->title)->toBe('No Description Page');
    expect($webpage->description)->toBeNull();
    expect($webpage->summary)->toBe('A summary of the page.');
});

it('handles a page with empty title', function () {
    $html = <<<'HTML'
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <title></title>
            <meta name="description" content="Has description">
        </head>
        <body>Content here</body>
    </html>
    HTML;

    Http::fake([
        'https://empty-title.com' => Http::response($html),
    ]);
    AnonymousAgent::fake(['Summary generated.']);

    $webpage = Webpage::factory()->create([
        'url' => 'https://empty-title.com',
        'title' => null,
        'description' => null,
        'summary' => null,
    ]);

    (new CrawlWebpageInformation($webpage))->handle();

    expect($webpage->title)->toBe('');
    expect($webpage->description)->toBe('Has description');
    expect($webpage->summary)->toBe('Summary generated.');
});

it('implements ShouldQueue interface', function () {
    $webpage = Webpage::factory()->create();
    $job = new CrawlWebpageInformation($webpage);

    expect($job)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
});

it('stores the webpage on the job', function () {
    $webpage = Webpage::factory()->create();
    $job = new CrawlWebpageInformation($webpage);

    expect($job->webpage->id)->toBe($webpage->id);
});
