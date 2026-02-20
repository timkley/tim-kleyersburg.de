<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\DeleteQuest;
use Modules\Holocron\Quest\Models\Quest;

it('deletes a quest', function () {
    $quest = Quest::factory()->create();

    (new DeleteQuest)->handle($quest);

    expect(Quest::find($quest->id))->toBeNull();
});
