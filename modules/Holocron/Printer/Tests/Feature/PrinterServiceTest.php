<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Modules\Holocron\Printer\Model\PrintQueue;
use Modules\Holocron\Printer\Services\Printer;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('can check if printer service is available', function () {
    $available = Printer::isAvailable();

    // Should be true in development environment with Node.js
    expect($available)->toBeTrue();
});

it('can print using a simple template', function () {
    // Skip if Node.js is not available
    if (! Printer::isAvailable()) {
        $this->markTestSkipped('Node.js not available for testing');
    }

    $printItem = Printer::print('holocron-printer::layout', [
        'content' => 'Test print content',
    ]);

    expect($printItem)->toBeInstanceOf(PrintQueue::class);
    expect($printItem->image)->not()->toBeEmpty();
    expect($printItem->actions)->toEqual([]);

    assertDatabaseCount('print_queues', 1);
    assertDatabaseHas('print_queues', [
        'id' => $printItem->id,
    ]);

    // Verify image file exists in public disk
    $imagePath = storage_path('app/public/'.$printItem->image);
    expect(file_exists($imagePath))->toBeTrue();

    // Verify it's a valid PNG
    $imageInfo = getimagesize($imagePath);
    expect($imageInfo)->not()->toBeFalse();
    expect($imageInfo['mime'])->toBe('image/png');
});

it('can print with data and actions', function () {
    if (! Printer::isAvailable()) {
        $this->markTestSkipped('Node.js not available for testing');
    }

    $data = ['title' => 'Test Quest', 'description' => 'A test quest item'];
    $actions = ['https://example.com/action1', 'https://example.com/action2'];

    $printItem = Printer::print('holocron-printer::layout', $data, $actions);

    expect($printItem)->toBeInstanceOf(PrintQueue::class);
    expect($printItem->actions)->toEqual($actions);

    assertDatabaseHas('print_queues', [
        'id' => $printItem->id,
        'actions' => json_encode($actions),
    ]);
});

it('generates unique filenames for different content', function () {
    if (! Printer::isAvailable()) {
        $this->markTestSkipped('Node.js not available for testing');
    }

    $printItem1 = Printer::print('holocron-printer::layout', ['content' => 'First item']);
    $printItem2 = Printer::print('holocron-printer::layout', ['content' => 'Second item']);

    expect($printItem1->image)->not()->toEqual($printItem2->image);

    // Both images should exist in public disk
    expect(file_exists(storage_path('app/public/'.$printItem1->image)))->toBeTrue();
    expect(file_exists(storage_path('app/public/'.$printItem2->image)))->toBeTrue();
});

it('handles node.js unavailable gracefully', function () {
    // Mock Process to simulate Node.js not being available
    Process::fake([
        'node --version' => Process::result(exitCode: 1),
    ]);

    expect(fn () => Printer::print('holocron-printer::layout', []))
        ->toThrow(Exception::class, 'Printer service is not available');

    assertDatabaseCount('print_queues', 0);
});

it('handles missing screenshot script gracefully', function () {
    // Temporarily rename the script to simulate it being missing
    $scriptPath = base_path('modules/Holocron/Printer/scripts/screenshot.js');
    $tempPath = $scriptPath.'.backup';

    if (file_exists($scriptPath)) {
        rename($scriptPath, $tempPath);
    }

    try {
        expect(fn () => Printer::print('holocron-printer::layout', []))
            ->toThrow(Exception::class, 'Printer service is not available');

        assertDatabaseCount('print_queues', 0);
    } finally {
        // Restore the script
        if (file_exists($tempPath)) {
            rename($tempPath, $scriptPath);
        }
    }
});

it('handles service errors gracefully', function () {
    if (! Printer::isAvailable()) {
        $this->markTestSkipped('Node.js not available for testing');
    }

    Log::spy();

    // Test with invalid view template to trigger error
    expect(fn () => Printer::print('non-existent-template', []))
        ->toThrow(Exception::class);

    // Should have logged the error
    Log::shouldHaveReceived('error')
        ->with('Printer service error', Mockery::type('array'))
        ->once();

    assertDatabaseCount('print_queues', 0);
});

it('fails when screenshot script fails', function () {
    // Mock Process to always fail
    Process::fake([
        'node --version' => Process::result(exitCode: 0, output: 'v16.0.0'),
        'node *' => Process::result(exitCode: 1, errorOutput: 'Screenshot generation failed'),
    ]);

    expect(fn () => Printer::print('holocron-printer::layout', []))
        ->toThrow(Exception::class, 'Screenshot script failed');

    assertDatabaseCount('print_queues', 0);
});

it('measures performance within target', function () {
    if (! Printer::isAvailable()) {
        $this->markTestSkipped('Node.js not available for testing');
    }

    $startTime = microtime(true);

    $printItem = Printer::print('holocron-printer::layout', [
        'content' => 'Performance benchmark test',
    ]);

    $duration = (microtime(true) - $startTime) * 1000; // Convert to ms

    expect($printItem)->toBeInstanceOf(PrintQueue::class);
    // Should complete in under 2 seconds (generous for CI environments)
    expect($duration)->toBeLessThan(2000);
});

it('creates storage directory if missing', function () {
    if (! Printer::isAvailable()) {
        $this->markTestSkipped('Node.js not available for testing');
    }

    // Remove the printer directory if it exists
    $printerDir = storage_path('app/public/printer');
    if (is_dir($printerDir)) {
        array_map('unlink', glob($printerDir.'/*'));
        rmdir($printerDir);
    }

    expect(is_dir($printerDir))->toBeFalse();

    $printItem = Printer::print('holocron-printer::layout', ['content' => 'Directory test']);

    expect(is_dir($printerDir))->toBeTrue();
    expect(file_exists(storage_path('app/public/'.$printItem->image)))->toBeTrue();
});
