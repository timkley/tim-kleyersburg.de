<?php

declare(strict_types=1);

use Livewire\Livewire;
use Modules\Holocron\Quest\Livewire\RecurringQuests;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('renders the recurring quests page', function () {
    Livewire::test(RecurringQuests::class)
        ->assertStatus(200);
});

it('displays recurring quests', function () {
    $quest = Quest::factory()->create(['name' => 'My Recurring Quest']);
    QuestRecurrence::factory()->create([
        'quest_id' => $quest->id,
        'every_x_days' => 3,
    ]);

    Livewire::test(RecurringQuests::class)
        ->assertSee('My Recurring Quest')
        ->assertSee('alle 3 Tage');
});

it('orders by interval days', function () {
    $weekly = Quest::factory()->create(['name' => 'Weekly Quest']);
    QuestRecurrence::factory()->create([
        'quest_id' => $weekly->id,
        'every_x_days' => 7,
    ]);

    $daily = Quest::factory()->create(['name' => 'Daily Quest']);
    QuestRecurrence::factory()->create([
        'quest_id' => $daily->id,
        'every_x_days' => 1,
    ]);

    Livewire::test(RecurringQuests::class)
        ->assertSeeInOrder(['Daily Quest', 'Weekly Quest']);
});

it('shows empty state when no recurring quests exist', function () {
    Livewire::test(RecurringQuests::class)
        ->assertDontSee('alle');
});
