<?php

declare(strict_types=1);

namespace Modules\Holocron\_Shared\Tests\Feature\Livewire\Components;

use App\Models\Scrobble;
use Livewire\Livewire;
use Modules\Holocron\_Shared\Livewire\Scrobbles;
use Modules\Holocron\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->user = User::factory()->create(['email' => 'timkley@gmail.com']);
});

it('is not reachable when unauthenticated', function () {
    get(route('holocron.scrobbles'))
        ->assertRedirect(route('holocron.login'));
});

it('renders the scrobbles page', function () {
    actingAs($this->user)
        ->get(route('holocron.scrobbles'))
        ->assertSuccessful()
        ->assertSeeLivewire(Scrobbles::class);
});

it('displays scrobbles', function () {
    Scrobble::query()->insert([
        'artist' => 'Radiohead',
        'track' => 'Creep',
        'album' => 'Pablo Honey',
        'played_at' => now()->subMinutes(5),
        'payload' => '{}',
    ]);

    Livewire::actingAs($this->user)
        ->test(Scrobbles::class)
        ->assertSee('Radiohead')
        ->assertSee('Creep');
});

it('displays scrobble count', function () {
    Scrobble::query()->insert([
        'artist' => 'Radiohead',
        'track' => 'Creep',
        'album' => 'Pablo Honey',
        'played_at' => now()->subMinutes(5),
        'payload' => '{}',
    ]);

    Scrobble::query()->insert([
        'artist' => 'Nirvana',
        'track' => 'Smells Like Teen Spirit',
        'album' => 'Nevermind',
        'played_at' => now()->subMinutes(10),
        'payload' => '{}',
    ]);

    Livewire::actingAs($this->user)
        ->test(Scrobbles::class)
        ->assertSee('2');
});

it('orders scrobbles by most recent first', function () {
    Scrobble::query()->insert([
        'artist' => 'Older Artist',
        'track' => 'Old Track',
        'album' => 'Old Album',
        'played_at' => now()->subHour(),
        'payload' => '{}',
    ]);

    Scrobble::query()->insert([
        'artist' => 'Newer Artist',
        'track' => 'New Track',
        'album' => 'New Album',
        'played_at' => now(),
        'payload' => '{}',
    ]);

    Livewire::actingAs($this->user)
        ->test(Scrobbles::class)
        ->assertSeeInOrder(['Newer Artist', 'Older Artist']);
});

it('shows zero scrobbles when none exist', function () {
    Livewire::actingAs($this->user)
        ->test(Scrobbles::class)
        ->assertSee('0');
});

it('paginates scrobbles at 100 per page', function () {
    for ($i = 0; $i < 105; $i++) {
        Scrobble::query()->insert([
            'artist' => "Artist {$i}",
            'track' => "Track {$i}",
            'album' => "Album {$i}",
            'played_at' => now()->subMinutes($i),
            'payload' => '{}',
        ]);
    }

    Livewire::actingAs($this->user)
        ->test(Scrobbles::class)
        ->assertSee('105')
        ->assertSee('Artist 0')
        ->assertDontSee('Artist 104');
});

it('has the correct page title', function () {
    actingAs($this->user)
        ->get(route('holocron.scrobbles'))
        ->assertSee('Scrobbles');
});
