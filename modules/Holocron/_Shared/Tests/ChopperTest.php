<?php

declare(strict_types=1);

use App\Ai\Agents\ChopperAgent;
use Illuminate\Support\Facades\DB;
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
        ->call('ask', 'Hallo Chopper!')
        ->assertSet('isStreaming', false);

    ChopperAgent::assertPrompted('Hallo Chopper!');
});

it('does not send empty messages', function () {
    ChopperAgent::fake();

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', '   ')
        ->call('send');

    ChopperAgent::assertNeverPrompted();
});

it('can start a new conversation', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Test')
        ->call('send')
        ->call('newConversation')
        ->assertSet('conversationId', null)
        ->assertSet('messages', [])
        ->assertSet('isStreaming', false);
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
