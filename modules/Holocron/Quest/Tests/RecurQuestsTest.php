<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Holocron\Quest\Enums\QuestRecurrenceType;
use Modules\Holocron\Quest\Jobs\RecurQuests;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;

uses(RefreshDatabase::class);

it('recurs daily quests', function () {
    $masterQuest = Quest::factory()->create();
    $recurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'type' => QuestRecurrenceType::Daily,
        'value' => 1,
        'last_recurred_at' => now()->subDay(),
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 2);
    $this->assertDatabaseHas('quests', [
        'name' => $masterQuest->name,
        'created_from_recurrence_id' => $recurrence->id,
    ]);
});

it('does not recur if not due', function () {
    $masterQuest = Quest::factory()->create();
    QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'type' => QuestRecurrenceType::Daily,
        'value' => 1,
        'last_recurred_at' => now(),
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 1);
});

it('does not recur if previous instance is not completed', function () {
    $masterQuest = Quest::factory()->create();
    $recurrence = QuestRecurrence::factory()->create([
        'quest_id' => $masterQuest->id,
        'type' => QuestRecurrenceType::Daily,
        'value' => 1,
        'last_recurred_at' => now()->subDay(),
    ]);
    Quest::factory()->create([
        'created_from_recurrence_id' => $recurrence->id,
    ]);

    (new RecurQuests)->handle();

    $this->assertDatabaseCount('quests', 2);
});
