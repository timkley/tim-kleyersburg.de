<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\MoveQuest;
use Modules\Holocron\Quest\Models\Quest;

it('moves a quest to a new parent', function () {
    $parent = Quest::factory()->create();
    $quest = Quest::factory()->create();

    $result = (new MoveQuest)->handle($quest, ['quest_id' => $parent->id]);

    expect($result->quest_id)->toBe($parent->id);
});

it('moves a quest to root by passing null', function () {
    $parent = Quest::factory()->create();
    $quest = Quest::factory()->create(['quest_id' => $parent->id]);

    $result = (new MoveQuest)->handle($quest, ['quest_id' => null]);

    expect($result->quest_id)->toBeNull();
});

it('validates that the parent quest must exist', function () {
    $quest = Quest::factory()->create();

    (new MoveQuest)->handle($quest, ['quest_id' => 99999]);
})->throws(Illuminate\Validation\ValidationException::class);
