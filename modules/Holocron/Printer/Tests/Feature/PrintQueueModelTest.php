<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Printer\Model\PrintQueue;
use Modules\Holocron\User\Models\User;

it('uses the PrintQueueFactory', function () {
    $item = PrintQueue::factory()->create();

    expect($item)->toBeInstanceOf(PrintQueue::class);
    expect($item->exists)->toBeTrue();
});

it('casts actions to array', function () {
    $item = PrintQueue::factory()->create([
        'actions' => ['https://example.com/action'],
    ]);

    $item->refresh();

    expect($item->actions)->toBeArray();
    expect($item->actions)->toBe(['https://example.com/action']);
});

it('casts printed_at to datetime', function () {
    $item = PrintQueue::factory()->create([
        'printed_at' => '2026-01-01 12:00:00',
    ]);

    $item->refresh();

    expect($item->printed_at)->toBeInstanceOf(Carbon\CarbonImmutable::class);
    expect($item->printed_at->year)->toBe(2026);
});

it('allows null printed_at', function () {
    $item = PrintQueue::factory()->create([
        'printed_at' => null,
    ]);

    expect($item->printed_at)->toBeNull();
});

it('has a printable morph relationship', function () {
    $user = User::factory()->create();

    $item = PrintQueue::factory()->create([
        'printable_type' => $user->getMorphClass(),
        'printable_id' => $user->id,
    ]);

    $item->refresh();

    expect($item->printable)->toBeInstanceOf(User::class);
    expect($item->printable->id)->toBe($user->id);
});

it('allows null printable', function () {
    $item = PrintQueue::factory()->create([
        'printable_type' => null,
        'printable_id' => null,
    ]);

    expect($item->printable)->toBeNull();
});

it('calculates length for text-based items', function () {
    $item = PrintQueue::factory()->create([
        'image' => null,
        'text' => "Line 1\nLine 2\nLine 3",
    ]);

    // topFeed (16) + lines (3) * lineHeightMm (4) + bottomFeed (10) = 38
    expect($item->length())->toBe(38);
});

it('calculates length for single-line text', function () {
    $item = PrintQueue::factory()->create([
        'image' => null,
        'text' => 'Single line',
    ]);

    // topFeed (16) + lines (1) * lineHeightMm (4) + bottomFeed (10) = 30
    expect($item->length())->toBe(30);
});

it('calculates length for image-based items', function () {
    Storage::fake('public');

    // Create a real 100px tall PNG image
    $image = imagecreatetruecolor(512, 100);
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    Storage::disk('public')->put('printer/test.png', $imageData);

    $item = PrintQueue::factory()->create([
        'image' => 'printer/test.png',
        'text' => null,
        'actions' => [],
    ]);

    // topFeed (16) + imageHeightInMm (round(100 / 7.25) = 14) + actionsInMm (0) + bottomFeed (10) = 40
    expect($item->length())->toBe(40);
});

it('calculates length for image-based items with actions', function () {
    Storage::fake('public');

    $image = imagecreatetruecolor(512, 100);
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    Storage::disk('public')->put('printer/test.png', $imageData);

    $item = PrintQueue::factory()->create([
        'image' => 'printer/test.png',
        'text' => null,
        'actions' => ['https://example.com/action1', 'https://example.com/action2'],
    ]);

    // topFeed (16) + imageHeightInMm (14) + actionsInMm (2 * 41 = 82) + bottomFeed (10) = 122
    expect($item->length())->toBe(122);
});

it('returns zero length when image file is missing', function () {
    Storage::fake('public');

    $item = PrintQueue::factory()->create([
        'image' => 'printer/nonexistent.png',
        'text' => null,
        'actions' => [],
    ]);

    expect($item->length())->toBe(0);
});

it('allows nullable image column', function () {
    $item = PrintQueue::factory()->create([
        'image' => null,
        'text' => 'Text only',
    ]);

    expect($item->image)->toBeNull();
});

it('allows nullable text column', function () {
    $item = PrintQueue::factory()->create([
        'text' => null,
    ]);

    expect($item->text)->toBeNull();
});
