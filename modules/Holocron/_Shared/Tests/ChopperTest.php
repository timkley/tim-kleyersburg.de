<?php

declare(strict_types=1);

use App\Ai\Agents\ChopperAgent;
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

it('can send a message and receive a response', function () {
    ChopperAgent::fake(['Hallo! Wie kann ich dir helfen?']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Hallo Chopper!')
        ->call('send')
        ->assertSet('message', '');

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
    ChopperAgent::fake(['Response']);

    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    Livewire::actingAs($user)
        ->test(Chopper::class)
        ->set('message', 'Test')
        ->call('send')
        ->call('newConversation')
        ->assertSet('conversationId', null)
        ->assertSet('messages', []);
});
