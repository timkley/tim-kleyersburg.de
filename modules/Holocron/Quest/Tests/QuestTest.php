<?php

declare(strict_types=1);

use Modules\Holocron\Bookmarks\Jobs\CrawlWebpageInformation;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;
use Prism\Prism\Prism;

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.quests'))
        ->assertRedirect(route('holocron.login'));
});

it('can construct a breadcrumb', function () {
    $quest = Quest::factory()->create();
    $quest2 = Quest::factory()->create(['quest_id' => $quest->id]);
    $quest3 = Quest::factory()->create(['quest_id' => $quest2->id]);

    $breadcrumb = $quest3->breadcrumb();
    expect($breadcrumb)->toHaveCount(2);
    expect($breadcrumb[0]['name'])->toBe($quest->name);
    expect($breadcrumb[1]['name'])->toBe($quest2->name);
});

it('can delete a quest', function () {
    $quest = Quest::factory()->create();

    Livewire::test('holocron.quest.components.item', ['quest' => $quest])
        ->call('deleteQuest', $quest->id);

    expect(Quest::count())->toBe(0);
});

it('has notes', function () {
    $quest = Quest::factory()->create();
    Note::factory()->for($quest)->count(3)->create();

    expect($quest->notes)->toHaveCount(3);
});

it('can add notes', function () {
    $quest = Quest::factory()->create();

    Livewire::test('holocron.quest.show', [$quest->id])
        ->set('noteDraft', 'test')
        ->call('addNote', $quest->id);

    expect($quest->notes)->toHaveCount(1);
});

it('can delete a note', function () {
    $quest = Quest::factory()->create();
    Note::factory()->for($quest)->create();

    Livewire::test('holocron.quest.show', [$quest->id])
        ->call('deleteNote', $quest->notes->first()->id);

    expect($quest->fresh()->notes->count())->toBe(0);
});

it('can find quests without children', function () {
    // Create a root quest with children
    $rootQuest = Quest::factory()->create();
    $childQuest1 = Quest::factory()->create(['quest_id' => $rootQuest->id]);
    $childQuest2 = Quest::factory()->create(['quest_id' => $rootQuest->id]);
    $childQuest3 = Quest::factory()->create(['quest_id' => $rootQuest->id]);

    // Create a leaf quest (child without children)
    $leafQuest1 = Quest::factory()->create(['quest_id' => $childQuest1->id]);

    // Create another leaf quest without children under childQuest2
    $leafQuest3 = Quest::factory()->create(['quest_id' => $childQuest2->id]);

    // Create an independent quest without children
    $leafQuest2 = Quest::factory()->create();

    $leafQuests = Quest::noChildren()->get();

    // Should find exactly 4 leaf quests now
    expect($leafQuests)->toHaveCount(4);

    // Verify the correct quests are identified as leaves
    expect($leafQuests->pluck('id')->all())
        ->toEqualCanonicalizing([$childQuest3->id, $leafQuest1->id, $leafQuest2->id, $leafQuest3->id]);

    // Verify that non-leaf quests are not included
    expect($leafQuests->pluck('id')->all())
        ->not->toContain($rootQuest->id)
        ->not->toContain($childQuest1->id)
        ->not->toContain($childQuest2->id);
});

it('can add links', function () {
    Prism::fake();
    Illuminate\Support\Facades\Bus::fake();
    $quest = Quest::factory()->create();
    Note::factory()->for($quest)->create();

    Livewire::test('holocron.quest.show', [$quest->id])
        ->set('linkDraft', 'https://example.com')
        ->call('addLink');

    Illuminate\Support\Facades\Bus::assertDispatched(CrawlWebpageInformation::class);
});

it('does not accumulate streamed content across multiple ai requests', function () {
    Prism::fake([
        \Prism\Prism\Testing\TextResponseFake::make()
            ->withText('First AI response'),
        \Prism\Prism\Testing\TextResponseFake::make()
            ->withText('Second AI response'),
    ]);

    $quest = Quest::factory()->create();

    $component = Livewire::test('holocron.quest.show', [$quest->id])
        ->set('chat', true);

    // Add first user note which triggers AI response
    $component->set('noteDraft', 'First question')
        ->call('addNote');

    // Simulate the first AI response streaming
    $firstNote = $quest->notes()->where('role', 'assistant')->first();
    $component->call('ask', $firstNote->id);

    // Verify first response content
    $firstNote->refresh();
    expect($firstNote->content)->toContain('First AI response');

    // Add second user note which triggers another AI response
    $component->set('noteDraft', 'Second question')
        ->call('addNote');

    // Simulate the second AI response streaming
    $secondNote = $quest->notes()->where('role', 'assistant')->latest()->first();
    $component->call('ask', $secondNote->id);

    // Verify second response doesn't contain first response content
    $secondNote->refresh();
    expect($secondNote->content)->toContain('Second AI response');
    expect($secondNote->content)->not->toContain('First AI response');
});
