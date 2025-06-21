<?php

declare(strict_types=1);

use App\Jobs\CrawlWebpageInformation;
use App\Models\Webpage;
use OpenAI\Responses\Chat\CreateResponse;

it('dispatches a job that crawls for more content', function () {
    Http::fake([
        'https://example.com' => Http::response(file_get_contents(base_path('tests/fixtures/example.html'))),
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
