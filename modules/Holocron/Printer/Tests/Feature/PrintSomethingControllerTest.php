<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Process;
use Modules\Holocron\Printer\Model\PrintQueue;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('creates a text-only print job', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->postJson('/api/holocron/printer/print', [
            'text' => 'Hello from the printer!',
        ]);

    $response->assertCreated()
        ->assertJson([
            'message' => 'Text print job created successfully.',
        ]);

    assertDatabaseCount('print_queues', 1);
    assertDatabaseHas('print_queues', [
        'text' => 'Hello from the printer!',
    ]);

    $item = PrintQueue::first();
    expect($item->text)->toBe('Hello from the printer!');
    expect($item->actions)->toBe([]);
    expect($item->image)->toBeNull();
});

it('creates an image-based print job when content is provided', function () {
    Process::fake([
        'node --version' => Process::result(exitCode: 0, output: 'v16.0.0'),
        'node *' => Process::result(exitCode: 0, output: ''),
    ]);

    // Pre-create the output directory and ensure the file will exist
    // after the faked process "runs" by hooking into Process::fake callback
    $outputDir = storage_path('app/public/printer');
    if (! is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    // Create a catch-all image file to satisfy the file_exists check
    // We freeze time so the filename is predictable
    $this->travelTo(now());
    $content = view('holocron-printer::print', ['content' => 'Some content to print'])->render();
    $timestamp = now()->format('Y-m-d_H-i-s-u');
    $hash = hash('sha256', $content);
    $filename = "print_{$timestamp}_{$hash}.png";
    file_put_contents("$outputDir/$filename", 'fake-image-content');

    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->postJson('/api/holocron/printer/print', [
            'content' => 'Some content to print',
        ]);

    $response->assertCreated()
        ->assertJson([
            'message' => 'Print job created successfully.',
        ]);

    // Clean up
    @unlink("$outputDir/$filename");
});

it('requires either content or text', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->postJson('/api/holocron/printer/print', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['content', 'text']);

    assertDatabaseCount('print_queues', 0);
});

it('validates content must be a string', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->postJson('/api/holocron/printer/print', [
            'content' => 123,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('content');
});

it('validates text must be a string', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->postJson('/api/holocron/printer/print', [
            'text' => ['not', 'a', 'string'],
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('text');
});

it('requires bearer token authentication', function () {
    $response = $this->postJson('/api/holocron/printer/print', [
        'text' => 'Hello',
    ]);

    $response->assertUnauthorized();
});

it('prefers text over content when both are provided', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->postJson('/api/holocron/printer/print', [
            'text' => 'Text content',
            'content' => 'Image content',
        ]);

    $response->assertCreated();

    $item = PrintQueue::first();
    expect($item->text)->toBe('Text content');
    expect($item->image)->toBeNull();
});
