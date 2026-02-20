<?php

declare(strict_types=1);

use Modules\Holocron\Quest\Actions\ToggleQuestComplete;
use Modules\Holocron\Quest\Models\Quest;

it('completes an incomplete quest', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    $result = (new ToggleQuestComplete)->handle($quest);

    expect($result->isCompleted())->toBeTrue()
        ->and($result->completed_at)->not->toBeNull();
});

it('uncompletes a completed quest', function () {
    $quest = Quest::factory()->create(['completed_at' => now()]);

    $result = (new ToggleQuestComplete)->handle($quest);

    expect($result->isCompleted())->toBeFalse()
        ->and($result->completed_at)->toBeNull();
});
