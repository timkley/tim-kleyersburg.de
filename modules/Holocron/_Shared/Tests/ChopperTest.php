<?php

declare(strict_types=1);

use App\Ai\Agents\ChopperAgent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\Chopper;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.chopper'))
        ->assertRedirect(route('holocron.login'));
});

it('renders the chopper page', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    actingAs($user)
        ->get(route('holocron.chopper'))
        ->assertSuccessful()
        ->assertSeeLivewire(Chopper::class);
});

it('uses mobile-safe layout classes so the chat pane can scroll', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    actingAs($user)
        ->get(route('holocron.chopper'))
        ->assertSuccessful()
        ->assertSee('h-[calc(100dvh-12rem)]', false)
        ->assertSee('min-h-0 min-w-0 flex-1 flex-col', false);
});

it('can send a message and trigger streaming', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Hallo Chopper!')
        ->call('send')
        ->assertSet('message', '')
        ->assertSet('isStreaming', true)
        ->assertCount('messages', 1);
});

it('can ask the agent and receive a response', function () {
    ChopperAgent::fake(['Hallo! Wie kann ich dir helfen?']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Hallo Chopper!')
        ->call('send')
        ->call('ask', 'Hallo Chopper!');

    ChopperAgent::assertQueued('Hallo Chopper!');
});

it('does not send empty messages', function () {
    ChopperAgent::fake();

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', '   ')
        ->call('send');

    ChopperAgent::assertNeverQueued();
});

it('dispatches message-sent event when sending a message', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Hallo Chopper!')
        ->call('send')
        ->assertDispatched('message-sent');
});

it('loads a conversation by route parameter', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    $conversationId = (string) Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $user->id,
        'title' => 'Test Conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('agent_conversation_messages')->insert([
        'id' => (string) Str::uuid(),
        'conversation_id' => $conversationId,
        'user_id' => $user->id,
        'agent' => 'chopper',
        'role' => 'user',
        'content' => 'Hello',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($user)
        ->get(route('holocron.chopper', $conversationId))
        ->assertSuccessful()
        ->assertSeeLivewire(Chopper::class);
});

it('returns 404 for another users conversation', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $otherUser = User::factory()->create();

    $conversationId = (string) Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $otherUser->id,
        'title' => 'Private Conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    actingAs($user)
        ->get(route('holocron.chopper', $conversationId))
        ->assertNotFound();
});

it('returns 404 for nonexistent conversation', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    actingAs($user)
        ->get(route('holocron.chopper', 'nonexistent-id'))
        ->assertNotFound();
});

it('can send a message with image attachments', function () {
    Storage::fake('local');
    ChopperAgent::fake(['Ich sehe das Bild!']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $file = UploadedFile::fake()->image('photo.jpg');

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Was siehst du?')
        ->set('attachments', [$file])
        ->call('send')
        ->assertSet('message', '')
        ->assertSet('attachments', [])
        ->assertSet('isStreaming', true);

    $storedFiles = Storage::disk('local')->allFiles('chopper-attachments');
    expect($storedFiles)->not->toBeEmpty();
});

it('validates attachments are images', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Check this')
        ->set('attachments', [$file])
        ->call('send')
        ->assertHasErrors('attachments.0');
});

it('validates max 5 attachments', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $files = array_map(fn () => UploadedFile::fake()->image('photo.jpg'), range(1, 6));

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Too many')
        ->set('attachments', $files)
        ->call('send')
        ->assertHasErrors('attachments');
});

it('passes attachments to the agent when asking', function () {
    Storage::fake('local');
    ChopperAgent::fake(['Ich sehe ein Bild!']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $file = UploadedFile::fake()->image('photo.jpg');

    $component = Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Beschreibe das Bild')
        ->set('attachments', [$file])
        ->call('send');

    $storedFiles = Storage::disk('local')->allFiles('chopper-attachments');
    expect($storedFiles)->toHaveCount(1);

    $component->call('ask', 'Beschreibe das Bild', $storedFiles);

    ChopperAgent::assertQueued('Beschreibe das Bild');
});

it('sends without attachments still works', function () {
    ChopperAgent::fake(['Hallo!']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Hallo Chopper!')
        ->call('send')
        ->assertSet('message', '')
        ->assertSet('isStreaming', true)
        ->assertCount('messages', 1);
});

it('continues an existing conversation when conversationId is set', function () {
    ChopperAgent::fake(['Weiter gehts!']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    $conversationId = (string) Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $user->id,
        'title' => 'Existing Conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(Chopper::class, ['conversationId' => $conversationId])
        ->set('message', 'Continue the conversation')
        ->call('send')
        ->call('ask', 'Continue the conversation');

    ChopperAgent::assertQueued('Continue the conversation');
});

it('removes an attachment by index', function () {
    Storage::fake('local');

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $file1 = UploadedFile::fake()->image('photo1.jpg');
    $file2 = UploadedFile::fake()->image('photo2.jpg');

    $component = Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('attachments', [$file1, $file2])
        ->assertCount('attachments', 2)
        ->call('removeAttachment', 0)
        ->assertCount('attachments', 1);
});

it('loads attachments from conversation history', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    $conversationId = (string) Str::uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $user->id,
        'title' => 'Test Conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('agent_conversation_messages')->insert([
        'id' => (string) Str::uuid(),
        'conversation_id' => $conversationId,
        'user_id' => $user->id,
        'agent' => 'chopper',
        'role' => 'user',
        'content' => 'Look at this image',
        'attachments' => json_encode([
            ['type' => 'stored-image', 'path' => 'chopper-attachments/test-photo.jpg', 'disk' => 'local'],
        ]),
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('agent_conversation_messages')->insert([
        'id' => (string) Str::uuid(),
        'conversation_id' => $conversationId,
        'user_id' => $user->id,
        'agent' => 'chopper',
        'role' => 'assistant',
        'content' => 'I see a photo!',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '{}',
        'meta' => '{}',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $component = Livewire::actingAs($user)
        ->test(Chopper::class, ['conversationId' => $conversationId]);

    $messages = $component->get('messages');
    expect($messages[0])->toHaveKey('attachments')
        ->and($messages[0]['attachments'])->toBe(['chopper-attachments/test-photo.jpg'])
        ->and($messages[1]['attachments'])->toBe([]);
});
