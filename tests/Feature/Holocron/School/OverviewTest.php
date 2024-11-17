<?php

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.school.information'))
        ->assertRedirect(route('holocron.login'));
});
