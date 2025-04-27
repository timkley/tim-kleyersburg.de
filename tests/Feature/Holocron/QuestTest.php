<?php

declare(strict_types=1);

use App\Models\Holocron\Quest;
use App\Models\Holocron\QuestNote;

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.quests'))
        ->assertRedirect(route('holocron.login'));
});

it('can add a quest', function () {
    Livewire::test('holocron.quests.overview')
        ->set('questDraft', 'test')
        ->call('addQuest');

    expect(Quest::count())->toBe(1);
    $quest = Quest::first();
    expect($quest->name)->toBe('test');
});

it('can construct a breadcrumb', function () {
    $quest = Quest::factory()->create();
    $quest2 = Quest::factory()->create(['quest_id' => $quest->id]);
    $quest3 = Quest::factory()->create(['quest_id' => $quest2->id]);

    $breadcrumb = $quest3->getBreadcrumb();
    expect($breadcrumb)->toHaveCount(3);
    expect($breadcrumb[0]['name'])->toBe($quest3->name);
    expect($breadcrumb[1]['name'])->toBe($quest2->name);
    expect($breadcrumb[2]['name'])->toBe($quest->name);
});

it('can delete a quest', function () {
    $quest = Quest::factory()->create();

    Livewire::test('holocron.quests.overview')
        ->call('deleteQuest', $quest->id);

    expect(Quest::count())->toBe(0);
});

it('has notes', function () {
    $quest = Quest::factory()->create();
    QuestNote::factory()->for($quest)->count(3)->create();

    expect($quest->notes)->toHaveCount(3);
});

it('can add notes', function () {
    $quest = Quest::factory()->create();

    Livewire::test('holocron.quests.overview', [$quest->id])
        ->set('noteDraft', 'test')
        ->call('addNote', $quest->id);

    expect($quest->notes)->toHaveCount(1);
});

it('can delete a note', function () {
    $quest = Quest::factory()->create();
    QuestNote::factory()->for($quest)->create();

    Livewire::test('holocron.quests.overview')
        ->call('deleteNote', $quest->notes->first()->id);

    expect($quest->fresh()->notes->count())->toBe(0);
});
