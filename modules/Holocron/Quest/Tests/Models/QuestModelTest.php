<?php

declare(strict_types=1);

use Modules\Holocron\Bookmarks\Models\Webpage;
use Modules\Holocron\Quest\Models\Note;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\Quest\Models\QuestRecurrence;
use Modules\Holocron\Quest\Models\Reminder;

it('marks a quest as completed via complete()', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    $quest->complete();

    expect($quest->fresh()->completed_at)->not->toBeNull();
});

it('reports completed status via isCompleted()', function () {
    $completed = Quest::factory()->create(['completed_at' => now()]);
    $incomplete = Quest::factory()->create(['completed_at' => null]);

    expect($completed->isCompleted())->toBeTrue()
        ->and($incomplete->isCompleted())->toBeFalse();
});

it('belongs to a parent quest', function () {
    $parent = Quest::factory()->create();
    $child = Quest::factory()->create(['quest_id' => $parent->id]);

    expect($child->parent)->toBeInstanceOf(Quest::class)
        ->and($child->parent->id)->toBe($parent->id);
});

it('returns null parent for root quests', function () {
    $quest = Quest::factory()->create(['quest_id' => null]);

    expect($quest->parent)->toBeNull();
});

it('has many children', function () {
    $parent = Quest::factory()->create();
    Quest::factory()->count(3)->create(['quest_id' => $parent->id]);

    expect($parent->children)->toHaveCount(3)
        ->each->toBeInstanceOf(Quest::class);
});

it('has many notes', function () {
    $quest = Quest::factory()->create();
    Note::factory()->count(2)->for($quest)->create();

    expect($quest->notes)->toHaveCount(2)
        ->each->toBeInstanceOf(Note::class);
});

it('belongs to many webpages', function () {
    $quest = Quest::factory()->create();
    $webpage = Webpage::factory()->create();
    $quest->webpages()->attach($webpage, ['title' => 'Test']);

    expect($quest->webpages)->toHaveCount(1)
        ->and($quest->webpages->first()->pivot->title)->toBe('Test');
});

it('has many reminders', function () {
    $quest = Quest::factory()->create();
    Reminder::factory()->count(2)->for($quest)->create();

    expect($quest->reminders)->toHaveCount(2)
        ->each->toBeInstanceOf(Reminder::class);
});

it('has one recurrence', function () {
    $quest = Quest::factory()->create();
    QuestRecurrence::factory()->for($quest)->create();

    expect($quest->recurrence)->toBeInstanceOf(QuestRecurrence::class);
});

it('belongs to a recurrence via instanceOf', function () {
    $quest = Quest::factory()->create();
    $recurrence = QuestRecurrence::factory()->for($quest)->create();
    $child = Quest::factory()->create(['created_from_recurrence_id' => $recurrence->id]);

    expect($child->instanceOf)->toBeInstanceOf(QuestRecurrence::class)
        ->and($child->instanceOf->id)->toBe($recurrence->id);
});

it('builds a breadcrumb from root to parent', function () {
    $root = Quest::factory()->create();
    $middle = Quest::factory()->create(['quest_id' => $root->id]);
    $leaf = Quest::factory()->create(['quest_id' => $middle->id]);

    $breadcrumb = $leaf->breadcrumb();

    expect($breadcrumb)->toHaveCount(2)
        ->and($breadcrumb[0]->id)->toBe($root->id)
        ->and($breadcrumb[1]->id)->toBe($middle->id);
});

it('returns empty breadcrumb for root quest', function () {
    $quest = Quest::factory()->create(['quest_id' => null]);

    expect($quest->breadcrumb())->toHaveCount(0);
});

it('includes current quest in breadcrumb when withCurrent is true', function () {
    $root = Quest::factory()->create();
    $child = Quest::factory()->create(['quest_id' => $root->id]);

    $breadcrumb = $child->breadcrumb(withCurrent: true);

    expect($breadcrumb)->toHaveCount(2)
        ->and($breadcrumb->last()->id)->toBe($child->id);
});

it('returns only current quest for root when withCurrent is true', function () {
    $quest = Quest::factory()->create(['quest_id' => null]);

    $breadcrumb = $quest->breadcrumb(withCurrent: true);

    expect($breadcrumb)->toHaveCount(1)
        ->and($breadcrumb->first()->id)->toBe($quest->id);
});

it('handles circular reference in breadcrumb', function () {
    $quest1 = Quest::factory()->create();
    $quest2 = Quest::factory()->create(['quest_id' => $quest1->id]);

    // Create a circular reference
    $quest1->update(['quest_id' => $quest2->id]);

    $breadcrumb = $quest1->breadcrumb();

    // Should not infinite loop, should break when cycle is detected
    expect($breadcrumb->count())->toBeLessThanOrEqual(2);
});

it('returns empty breadcrumb for self-referencing quest', function () {
    $quest = Quest::factory()->create();
    $quest->update(['quest_id' => $quest->id]);

    expect($quest->breadcrumb())->toHaveCount(0);
});

it('returns only current quest in breadcrumb for self-referencing quest when withCurrent is true', function () {
    $quest = Quest::factory()->create();
    $quest->update(['quest_id' => $quest->id]);

    $breadcrumb = $quest->breadcrumb(withCurrent: true);

    expect($breadcrumb)->toHaveCount(1)
        ->and($breadcrumb->first()->id)->toBe($quest->id);
});

it('returns empty breadcrumb when parent does not exist', function () {
    $quest = Quest::factory()->create(['quest_id' => 99999]);

    $breadcrumb = $quest->breadcrumb();

    expect($breadcrumb)->toHaveCount(0);
});

it('generates a searchable array', function () {
    $quest = Quest::factory()->create([
        'name' => 'Test Quest',
        'date' => '2025-06-01',
    ]);

    $searchable = $quest->toSearchableArray();

    expect($searchable)
        ->toHaveKey('id')
        ->toHaveKey('name')
        ->toHaveKey('date')
        ->toHaveKey('breadcrumb')
        ->toHaveKey('created_at')
        ->and($searchable['id'])->toBeString()
        ->and($searchable['name'])->toBe('Test Quest')
        ->and($searchable['breadcrumb'])->toBeString();
});

it('scopes notCompleted quests', function () {
    Quest::factory()->create(['completed_at' => now()]);
    Quest::factory()->create(['completed_at' => null]);

    expect(Quest::notCompleted()->count())->toBe(1);
});

it('scopes completed quests', function () {
    Quest::factory()->create(['completed_at' => now()]);
    Quest::factory()->create(['completed_at' => null]);

    expect(Quest::completed()->count())->toBe(1);
});

it('scopes noChildren quests', function () {
    $parent = Quest::factory()->create(['completed_at' => null]);
    Quest::factory()->create(['quest_id' => $parent->id, 'completed_at' => null]);
    $standalone = Quest::factory()->create(['completed_at' => null]);

    $results = Quest::noChildren()->get();

    expect($results->pluck('id'))->toContain($standalone->id)
        ->and($results->pluck('id'))->not->toContain($parent->id);
});

it('scopes notDaily quests', function () {
    Quest::factory()->create(['date' => '2025-06-01']);
    Quest::factory()->create(['date' => null]);

    expect(Quest::notDaily()->count())->toBe(1);
});

it('scopes today quests', function () {
    Quest::factory()->create(['date' => today(), 'daily' => false]);
    Quest::factory()->create(['date' => today()->addDay(), 'daily' => false]);
    Quest::factory()->create(['date' => today(), 'daily' => true]);

    expect(Quest::today()->count())->toBe(1);
});

it('scopes notToday quests', function () {
    Quest::factory()->create(['date' => today(), 'daily' => false]);
    Quest::factory()->create(['date' => null, 'daily' => false]);
    Quest::factory()->create(['date' => null, 'daily' => true]);

    expect(Quest::notToday()->count())->toBe(1);
});

it('scopes areNotNotes quests', function () {
    Quest::factory()->create(['is_note' => false]);
    Quest::factory()->create(['is_note' => true]);

    expect(Quest::areNotNotes()->count())->toBe(1);
});

it('scopes areNotes quests', function () {
    Quest::factory()->create(['is_note' => false]);
    Quest::factory()->create(['is_note' => true]);

    expect(Quest::areNotes()->count())->toBe(1);
});

it('casts date as date', function () {
    $quest = Quest::factory()->create(['date' => '2025-06-01']);

    expect($quest->date)->toBeInstanceOf(Carbon\CarbonImmutable::class)
        ->and($quest->date->format('Y-m-d'))->toBe('2025-06-01');
});

it('casts daily as boolean', function () {
    $quest = Quest::factory()->create(['daily' => 1]);

    expect($quest->daily)->toBeTrue()->toBeBool();
});

it('casts attachments as collection', function () {
    $quest = Quest::factory()->create(['attachments' => ['file1.jpg', 'file2.png']]);

    expect($quest->attachments)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($quest->attachments)->toHaveCount(2);
});

it('casts completed_at as datetime', function () {
    $quest = Quest::factory()->create(['completed_at' => '2025-06-01 12:00:00']);

    expect($quest->completed_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
});

it('casts is_note as boolean', function () {
    $quest = Quest::factory()->create(['is_note' => 1]);

    expect($quest->is_note)->toBeTrue()->toBeBool();
});

it('removes pending print queue entries when quest is completed', function () {
    $quest = Quest::factory()->create(['completed_at' => null, 'should_be_printed' => false]);

    Modules\Holocron\Printer\Model\PrintQueue::create([
        'printable_type' => Quest::class,
        'printable_id' => $quest->id,
        'image' => 'test.png',
        'actions' => [],
        'printed_at' => null,
    ]);

    $quest->update(['completed_at' => now()]);

    expect(Modules\Holocron\Printer\Model\PrintQueue::query()
        ->where('printable_type', Quest::class)
        ->where('printable_id', $quest->id)
        ->count()
    )->toBe(0);
});

it('does not remove already printed queue entries when quest is completed', function () {
    $quest = Quest::factory()->create(['completed_at' => null, 'should_be_printed' => false]);

    Modules\Holocron\Printer\Model\PrintQueue::create([
        'printable_type' => Quest::class,
        'printable_id' => $quest->id,
        'image' => 'test.png',
        'actions' => [],
        'printed_at' => now(),
    ]);

    $quest->update(['completed_at' => now()]);

    expect(Modules\Holocron\Printer\Model\PrintQueue::query()
        ->where('printable_type', Quest::class)
        ->where('printable_id', $quest->id)
        ->count()
    )->toBe(1);
});
