<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Holocron\Printer\Model\PrintQueue;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

beforeEach(function () {
    Storage::fake('public');
    $user = User::factory(['email' => 'timkley@gmail.com'])->create();
    UserSetting::factory()->create(['user_id' => $user->id]);
});

it('returns empty array when no print queue items exist', function () {
    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->getJson('/api/holocron/printer/queue');

    $response->assertOk()
        ->assertExactJson([]);
});

it('returns print queue items with image URLs', function () {
    // Create a fake image file
    Storage::disk('public')->put('printer/test_image.png', 'fake image content');

    $printItem = PrintQueue::create([
        'image' => 'printer/test_image.png',
        'actions' => ['action1' => 'value1'],
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->getJson('/api/holocron/printer/queue');

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonStructure([
            '*' => ['id', 'image', 'actions', 'created_at'],
        ]);

    $data = $response->json();
    expect($data[0]['id'])->toBe($printItem->id);
    expect($data[0]['image'])->toStartWith(config('app.url').'/storage/printer/');
    expect($data[0]['actions'])->toBe(['action1' => 'value1']);
});

it('skips items with missing image files', function () {
    PrintQueue::create([
        'image' => 'printer/missing_image.png',
        'actions' => [],
    ]);

    // Also create a valid item
    Storage::disk('public')->put('printer/valid_image.png', 'valid content');
    $validItem = PrintQueue::create([
        'image' => 'printer/valid_image.png',
        'actions' => [],
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->getJson('/api/holocron/printer/queue');

    $response->assertOk()
        ->assertJsonCount(1);

    $data = $response->json();
    expect($data[0]['id'])->toBe($validItem->id);
});

it('marks items as printed after successful retrieval', function () {
    Storage::disk('public')->put('printer/test_image.png', 'fake image content');

    $printItem = PrintQueue::create([
        'image' => 'printer/test_image.png',
        'actions' => [],
    ]);

    expect($printItem->fresh()->printed_at)->toBeNull();

    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->getJson('/api/holocron/printer/queue');

    $response->assertOk();

    expect($printItem->fresh()->printed_at)->not->toBeNull();
});

it('does not return already printed items', function () {
    Storage::disk('public')->put('printer/test_image.png', 'fake image content');

    // Create already printed item
    PrintQueue::create([
        'image' => 'printer/test_image.png',
        'actions' => [],
        'printed_at' => now(),
    ]);

    // Create unprinted item
    PrintQueue::create([
        'image' => 'printer/test_image.png',
        'actions' => [],
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->getJson('/api/holocron/printer/queue');

    $response->assertOk()
        ->assertJsonCount(1); // Only the unprinted item
});

it('orders items by creation time', function () {
    Storage::disk('public')->put('printer/test_image.png', 'fake image content');

    // Create items with specific timestamps
    $first = PrintQueue::create([
        'image' => 'printer/test_image.png',
        'actions' => [],
        'created_at' => now()->subMinutes(5),
    ]);

    $second = PrintQueue::create([
        'image' => 'printer/test_image.png',
        'actions' => [],
        'created_at' => now()->subMinutes(2),
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->getJson('/api/holocron/printer/queue');

    $response->assertOk()
        ->assertJsonCount(2);

    $data = $response->json();
    expect($data[0]['id'])->toBe($first->id); // Oldest first
    expect($data[1]['id'])->toBe($second->id);
});

it('handles concurrent requests with cache lock', function () {
    Storage::disk('public')->put('printer/test_image.png', 'fake image content');

    PrintQueue::create([
        'image' => 'printer/test_image.png',
        'actions' => [],
    ]);

    // Simulate lock acquisition by manually locking
    $lock = Cache::lock('print-queue', 60);
    $lock->get();

    try {
        $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
            ->getJson('/api/holocron/printer/queue');

        $response->assertOk()
            ->assertExactJson([]); // Should return empty when locked
    } finally {
        $lock->release();
    }
});

it('returns absolute URLs for external printer access', function () {
    Storage::disk('public')->put('printer/absolute_test.png', 'test content');

    $printItem = PrintQueue::create([
        'image' => 'printer/absolute_test.png',
        'actions' => [],
    ]);

    $response = $this->withHeaders(['Authorization' => 'Bearer '.config('auth.bearer_token')])
        ->getJson('/api/holocron/printer/queue');

    $response->assertOk();

    $data = $response->json();
    $imageUrl = $data[0]['image'];
    $appUrl = parse_url(config('app.url'));

    // Should be absolute URL
    expect($imageUrl)->toStartWith($appUrl['scheme'].'://');
    // Should contain the full domain from config
    expect($imageUrl)->toContain($appUrl['host']);
    // Should be a complete URL, not relative
    expect(parse_url($imageUrl, PHP_URL_HOST))->toBe($appUrl['host']);
    expect(parse_url($imageUrl, PHP_URL_SCHEME))->toBe($appUrl['scheme']);
});

it('requires bearer token authentication', function () {
    $response = $this->getJson('/api/holocron/printer/queue');

    $response->assertUnauthorized();
});
