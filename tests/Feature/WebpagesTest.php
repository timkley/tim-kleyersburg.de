<?php

declare(strict_types=1);

use App\Jobs\CrawlWebpageInformation;
use App\Models\Webpage;
use OpenAI\Responses\Chat\CreateResponse;

it('dispatches a job that crawls for more content', function () {
    Http::fake([
        'https://firecrawl.wacg.dev/*' => Http::response(file_get_contents(base_path('tests/fixtures/example.json'))),
    ]);
    Denk::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Good day sir!!',
                    ],
                ],
            ],
        ]),
    ]);
    $bookmark = Webpage::factory()->create([
        'url' => 'https://example.com',
        'title' => null,
        'description' => null,
        'summary' => null,
    ]);
    (new CrawlWebpageInformation($bookmark))->handle();

    expect($bookmark->title)->toBe('Example title');
    expect($bookmark->description)->toBe('Example description');
    expect($bookmark->summary)->toBe('Good day sir!!');
});
