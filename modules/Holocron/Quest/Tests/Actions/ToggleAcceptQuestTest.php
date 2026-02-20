<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\ToggleAcceptQuest;
use Modules\Holocron\Quest\Models\Quest;

it('accepts an unaccepted quest by setting a date', function () {
    $quest = Quest::factory()->create(['date' => null]);

    $result = (new ToggleAcceptQuest)->handle($quest);

    expect($result->date)->not->toBeNull();
});

it('unaccepts an accepted quest by clearing the date', function () {
    $quest = Quest::factory()->create(['date' => now()]);

    $result = (new ToggleAcceptQuest)->handle($quest);

    expect($result->date)->toBeNull();
});
