<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.helpers.discord-icon'))
        ->assertRedirect(route('holocron.login'));
});
