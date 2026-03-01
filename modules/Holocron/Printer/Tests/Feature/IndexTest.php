<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Holocron\Printer\Model\PrintQueue;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $user = User::factory()->create();
    actingAs($user);
});

it('is not reachable when unauthenticated', function () {
    auth()->logout();

    get(route('holocron.printer.queue'))
        ->assertRedirect();
});

it('renders successfully', function () {
    Livewire::test('holocron.printer.index')
        ->assertSuccessful();
});

it('displays print queue items', function () {
    Storage::fake('public');

    // Create a real PNG image so length() can parse it
    $image = imagecreatetruecolor(512, 100);
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    imagedestroy($image);

    Storage::disk('public')->put('printer/test.png', $imageData);

    PrintQueue::factory()->create([
        'image' => 'printer/test.png',
        'actions' => [],
    ]);

    Livewire::test('holocron.printer.index')
        ->assertSuccessful()
        ->assertSee('1 Druckaufträge');
});

it('displays text-based print queue items', function () {
    PrintQueue::factory()->create([
        'image' => null,
        'text' => "Hello\nWorld",
        'actions' => [],
    ]);

    Livewire::test('holocron.printer.index')
        ->assertSuccessful()
        ->assertSee('Hello');
});

it('shows empty state when no items exist', function () {
    Livewire::test('holocron.printer.index')
        ->assertSuccessful()
        ->assertSee('No items in print queue');
});

it('paginates results at 20 items per page', function () {
    PrintQueue::factory()->count(25)->create();

    Livewire::test('holocron.printer.index')
        ->assertSuccessful()
        ->assertSee('25 Druckaufträge');
});

it('orders items by creation date descending', function () {
    $older = PrintQueue::factory()->create([
        'text' => 'Older item',
        'image' => null,
        'created_at' => now()->subHour(),
    ]);

    $newer = PrintQueue::factory()->create([
        'text' => 'Newer item',
        'image' => null,
        'created_at' => now(),
    ]);

    Livewire::test('holocron.printer.index')
        ->assertSuccessful()
        ->assertSeeInOrder(['Newer item', 'Older item']);
});

it('shows printed status badge', function () {
    PrintQueue::factory()->create([
        'text' => 'Printed item',
        'image' => null,
        'printed_at' => now(),
    ]);

    Livewire::test('holocron.printer.index')
        ->assertSuccessful()
        ->assertSee(now()->format('d.m.Y H:i'));
});

it('shows pending badge for unprinted items', function () {
    PrintQueue::factory()->create([
        'text' => 'Pending item',
        'image' => null,
        'printed_at' => null,
    ]);

    Livewire::test('holocron.printer.index')
        ->assertSuccessful()
        ->assertSee('Pending');
});
