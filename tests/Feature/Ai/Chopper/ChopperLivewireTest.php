<?php

declare(strict_types=1);

use App\Ai\Agents\ChopperAgent;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\Chopper;
use Modules\Holocron\User\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    ChopperAgent::fake();
});

it('dispatches chopper-subscribe event with conversation id on send', function () {
    Livewire::test(Chopper::class)
        ->set('message', 'Hello Chopper')
        ->call('send')
        ->assertDispatched('chopper-subscribe');
});

it('generates a temp UUID for new conversations on send', function () {
    $component = Livewire::test(Chopper::class)
        ->set('message', 'Hello Chopper')
        ->call('send');

    expect($component->get('conversationId'))->not->toBeNull()
        ->and($component->get('conversationId'))->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
});

it('sets isStreaming to true on send', function () {
    $component = Livewire::test(Chopper::class)
        ->set('message', 'Hello Chopper')
        ->call('send');

    expect($component->get('isStreaming'))->toBeTrue();
});

it('adds user message to messages array on send', function () {
    $component = Livewire::test(Chopper::class)
        ->set('message', 'Hello Chopper')
        ->call('send');

    $messages = $component->get('messages');

    expect($messages)->toHaveCount(1)
        ->and($messages[0]['role'])->toBe('user')
        ->and($messages[0]['content'])->toBe('Hello Chopper');
});

it('does not send when message is empty', function () {
    Livewire::test(Chopper::class)
        ->set('message', '')
        ->call('send')
        ->assertNotDispatched('chopper-subscribe');
});

it('calls broadcastOnQueue on ask with new conversation', function () {
    Livewire::test(Chopper::class)
        ->set('message', 'Hello Chopper')
        ->call('send')
        ->call('ask', 'Hello Chopper', []);

    ChopperAgent::assertQueued('Hello Chopper');
});

it('calls broadcastOnQueue on ask with existing conversation', function () {
    $conversationId = fake()->uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $this->user->id,
        'title' => 'Test conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(Chopper::class, ['conversationId' => $conversationId])
        ->set('message', 'Follow up question')
        ->call('send')
        ->call('ask', 'Follow up question', []);

    ChopperAgent::assertQueued('Follow up question');
});

it('resolves temp UUID to real conversation ID on streamCompleted', function () {
    $component = Livewire::test(Chopper::class)
        ->set('message', 'Hello Chopper')
        ->call('send');

    // Simulate the AI creating a real conversation
    $realId = fake()->uuid();
    DB::table('agent_conversations')->insert([
        'id' => $realId,
        'user_id' => $this->user->id,
        'title' => 'Test conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $component->call('streamCompleted');

    expect($component->get('conversationId'))->toBe($realId)
        ->and($component->get('isStreaming'))->toBeFalse()
        ->and($component->get('streamedResponse'))->toBe('');
});

it('keeps real conversation ID on streamCompleted', function () {
    $conversationId = fake()->uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $this->user->id,
        'title' => 'Test conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $component = Livewire::test(Chopper::class, ['conversationId' => $conversationId])
        ->set('message', 'Hello')
        ->call('send')
        ->call('streamCompleted');

    expect($component->get('conversationId'))->toBe($conversationId);
});

it('resets streaming state on handleStreamError', function () {
    $component = Livewire::test(Chopper::class)
        ->set('message', 'Hello')
        ->call('send')
        ->call('handleStreamError', 'Something went wrong');

    expect($component->get('isStreaming'))->toBeFalse()
        ->and($component->get('streamedResponse'))->toBe('');
});

it('allows conversation owner to access channel', function () {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app-id',
    ]);

    $conversationId = fake()->uuid();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $this->user->id,
        'title' => 'Test conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/broadcasting/auth', [
        'channel_name' => 'private-chopper.conversation.'.$conversationId,
        'socket_id' => '12345.67890',
    ])->assertSuccessful();
});

it('denies non-owner access to channel', function () {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app-id',
    ]);

    $conversationId = fake()->uuid();
    $otherUser = User::factory()->create();
    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'user_id' => $otherUser->id,
        'title' => 'Other user conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->postJson('/broadcasting/auth', [
        'channel_name' => 'private-chopper.conversation.'.$conversationId,
        'socket_id' => '12345.67890',
    ])->assertForbidden();
});

it('allows authenticated user to access temp UUID channel', function () {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app-id',
    ]);

    $tempId = fake()->uuid();

    $this->postJson('/broadcasting/auth', [
        'channel_name' => 'private-chopper.conversation.'.$tempId,
        'socket_id' => '12345.67890',
    ])->assertSuccessful();
});
