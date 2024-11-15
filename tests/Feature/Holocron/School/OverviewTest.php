<?php

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.school.index'))
        ->assertRedirect(route('holocron.login'));
});
