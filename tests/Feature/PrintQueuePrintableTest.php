<?php

declare(strict_types=1);

use Modules\Holocron\Printer\Model\PrintQueue;
use Modules\Holocron\Printer\Services\Printer;
use Modules\Holocron\Quest\Models\Quest;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('has a polymorphic printable relationship', function () {
    $quest = Quest::factory()->create();
    $printQueue = PrintQueue::factory()->create([
        'printable_type' => Quest::class,
        'printable_id' => $quest->id,
    ]);

    expect($printQueue->printable)->toBeInstanceOf(Quest::class);
    expect($printQueue->printable->id)->toBe($quest->id);
});

it('can have null printable', function () {
    $printQueue = PrintQueue::factory()->create([
        'printable_type' => null,
        'printable_id' => null,
    ]);

    expect($printQueue->printable)->toBeNull();
});

it('associates printable when passed to Printer::print()', function () {
    if (! Printer::isAvailable()) {
        $this->markTestSkipped('Node.js not available for testing');
    }

    $quest = Quest::factory()->create();

    $printItem = Printer::print(
        'holocron-printer::layout',
        ['content' => 'Test print content'],
        [],
        $quest
    );

    expect($printItem->printable_type)->toBe(Quest::class);
    expect($printItem->printable_id)->toBe($quest->id);

    assertDatabaseHas('print_queues', [
        'id' => $printItem->id,
        'printable_type' => Quest::class,
        'printable_id' => $quest->id,
    ]);
});

it('does not set printable when not provided to Printer::print()', function () {
    if (! Printer::isAvailable()) {
        $this->markTestSkipped('Node.js not available for testing');
    }

    $printItem = Printer::print(
        'holocron-printer::layout',
        ['content' => 'Test print content'],
        []
    );

    expect($printItem->printable_type)->toBeNull();
    expect($printItem->printable_id)->toBeNull();
});

it('deletes unprinted queue entries when quest is completed', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);
    $printQueue = PrintQueue::factory()->create([
        'printable_type' => Quest::class,
        'printable_id' => $quest->id,
        'printed_at' => null,
    ]);

    assertDatabaseHas('print_queues', ['id' => $printQueue->id]);

    $quest->complete();

    assertDatabaseMissing('print_queues', ['id' => $printQueue->id]);
});

it('does not delete already printed queue entries when quest is completed', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);
    $printQueue = PrintQueue::factory()->create([
        'printable_type' => Quest::class,
        'printable_id' => $quest->id,
        'printed_at' => now(),
    ]);

    assertDatabaseHas('print_queues', ['id' => $printQueue->id]);

    $quest->complete();

    assertDatabaseHas('print_queues', ['id' => $printQueue->id]);
});

it('does not delete unrelated queue entries when quest is completed', function () {
    $quest1 = Quest::factory()->create(['completed_at' => null]);
    $quest2 = Quest::factory()->create(['completed_at' => null]);

    $printQueue1 = PrintQueue::factory()->create([
        'printable_type' => Quest::class,
        'printable_id' => $quest1->id,
        'printed_at' => null,
    ]);

    $printQueue2 = PrintQueue::factory()->create([
        'printable_type' => Quest::class,
        'printable_id' => $quest2->id,
        'printed_at' => null,
    ]);

    $quest1->complete();

    assertDatabaseMissing('print_queues', ['id' => $printQueue1->id]);
    assertDatabaseHas('print_queues', ['id' => $printQueue2->id]);
});

it('does not delete queue entries without printable when quest is completed', function () {
    $quest = Quest::factory()->create(['completed_at' => null]);

    $printQueueWithoutPrintable = PrintQueue::factory()->create([
        'printable_type' => null,
        'printable_id' => null,
        'printed_at' => null,
    ]);

    $quest->complete();

    assertDatabaseHas('print_queues', ['id' => $printQueueWithoutPrintable->id]);
});
