<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Modules\Holocron\School\Jobs\CheckForNewThings;

it('dispatches the CheckForNewThings job', function () {
    Bus::fake();

    $this->artisan('t')->assertSuccessful();

    Bus::assertDispatched(CheckForNewThings::class);
});
