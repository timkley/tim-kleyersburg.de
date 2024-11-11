<?php

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
