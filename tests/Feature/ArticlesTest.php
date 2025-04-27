<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('shows the article overview', function () {
    get('/articles')
        ->assertSuccessful();
});

it('shows an article', function () {
    get('/articles/resize-images-with-mogrify')
        ->assertSuccessful()
        ->assertSee('Use ImageMagicks mogrify CLI to batch resize images');
});

it('makes sure the feed is working', function () {
    get('/feed.xml')
        ->assertSuccessful();
});
