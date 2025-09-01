<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Livewire\Show;
use Modules\Holocron\Quest\Models\Quest;

test('deleteLink removes pivot relationship without deleting webpage', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();

    // Attach the webpage to the quest with a custom title
    $quest->webpages()->attach($webpage, ['title' => 'Test Link']);

    // Verify the relationship exists
    expect($quest->webpages)->toHaveCount(1);
    expect($webpage->exists())->toBeTrue();

    // Get the pivot ID
    $pivotId = $quest->webpages()->first()->pivot->id;

    // Test the deleteLink method
    Livewire::test(Show::class, ['quest' => $quest])
        ->call('deleteLink', $pivotId)
        ->assertStatus(200);

    // Refresh the models
    $quest->refresh();
    $webpage->refresh();

    // Verify the pivot relationship is removed
    expect($quest->webpages)->toHaveCount(0);

    // Verify the webpage still exists
    expect($webpage->exists())->toBeTrue();
});
