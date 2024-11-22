<?php

use function Pest\Laravel\get;

it('can show all pages', function (string $route) {
    get($route)->assertSuccessful();
})->with([
    fn () => route('pages.home'),
    fn () => route('pages.einmaleins'),
]);