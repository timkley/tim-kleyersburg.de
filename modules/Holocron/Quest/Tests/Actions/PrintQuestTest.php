<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\PrintQuest;
use Modules\Holocron\Quest\Models\Quest;

it('sets should_be_printed to true', function () {
    $quest = Quest::factory()->create(['should_be_printed' => false]);

    $result = (new PrintQuest)->handle($quest);

    expect((bool) $result->should_be_printed)->toBeTrue();
});
