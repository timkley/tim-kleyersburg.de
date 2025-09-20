<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Tests;

use Modules\Holocron\Quest\Models\Quest;

it('deletes quests that are marked as delete after print', function () {
    Quest::factory()->create(['name' => 'Test Quest 1', 'should_be_printed' => true, 'delete_after_print' => true]);
    Quest::factory()->create(['name' => 'Test Quest 2', 'should_be_printed' => true, 'delete_after_print' => false]);

    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->get(route('holocron.quests.print-queue'));

    $this->assertDatabaseMissing('quests', ['name' => 'Test Quest 1']);
    $this->assertDatabaseHas('quests', ['name' => 'Test Quest 2']);
});

it('updates the printed_at timestamp for all quests in the print queue', function () {
    $quest1 = Quest::factory()->create(['should_be_printed' => true, 'delete_after_print' => true]);
    $quest2 = Quest::factory()->create(['should_be_printed' => true, 'delete_after_print' => false]);

    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->get(route('holocron.quests.print-queue'));

    $this->assertDatabaseHas('quests', ['id' => $quest2->id, 'printed_at' => now()->toDateTimeString()]);
});
