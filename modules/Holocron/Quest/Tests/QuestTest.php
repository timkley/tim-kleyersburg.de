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

it('can add a quest', function () {
    Livewire::test('holocron.quest.components.main-quests')
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

    $breadcrumb = $quest3->breadcrumb();
    expect($breadcrumb)->toHaveCount(3);
    expect($breadcrumb[0]['name'])->toBe($quest->name);
    expect($breadcrumb[1]['name'])->toBe($quest2->name);
    expect($breadcrumb[2]['name'])->toBe($quest3->name);
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

it('only shows past quests for future dates in daily agenda', function () {
    // Arrange
    $yesterdayQuest = Quest::factory()->create(['date' => now()->subDay()]);
    $yesterdaySubQuest = Quest::factory()->create(['quest_id' => $yesterdayQuest->id]);

    $todayQuest = Quest::factory()->create(['date' => now()]);
    $todaySubQuest = Quest::factory()->create(['quest_id' => $todayQuest->id]);

    $tomorrowQuest = Quest::factory()->create(['date' => now()->addDay()]);
    $tomorrowSubQuest = Quest::factory()->create(['quest_id' => $tomorrowQuest->id]);

    // Act & Assert
    // Viewing yesterday's quest should only show yesterday's subquests
    Livewire::test('holocron.quest.show', [$yesterdayQuest->id])
        ->assertViewHas('questChildren', function ($questChildren) use ($yesterdaySubQuest) {
            expect($questChildren)->toHaveCount(1)
                ->and($questChildren->first()->id)->toBe($yesterdaySubQuest->id);

            return true;
        });

    // Viewing today's quest should show yesterday's and today's subquests
    Livewire::test('holocron.quest.show', [$todayQuest->id])
        ->assertViewHas('questChildren', function ($questChildren) use ($yesterdaySubQuest, $todaySubQuest) {
            expect($questChildren)->toHaveCount(2)
                ->and($questChildren->pluck('id')->all())->toEqualCanonicalizing([$yesterdaySubQuest->id, $todaySubQuest->id]);

            return true;
        });

    // Viewing tomorrow's quest should show all three
    Livewire::test('holocron.quest.show', [$tomorrowQuest->id])
        ->assertViewHas('questChildren', function ($questChildren) use ($yesterdaySubQuest, $todaySubQuest, $tomorrowSubQuest) {
            expect($questChildren)->toHaveCount(3)
                ->and($questChildren->pluck('id')->all())->toEqualCanonicalizing([$yesterdaySubQuest->id, $todaySubQuest->id, $tomorrowSubQuest->id]);

            return true;
        });
});
