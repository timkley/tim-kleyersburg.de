<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $user = User::factory()
        ->has(UserSetting::factory(), 'settings')
        ->create();

    actingAs($user);

    Cache::forget('apod');
});

it('renders the apod component with data from nasa api', function () {
    Http::fake([
        'https://api.nasa.gov/*' => Http::response([
            'url' => 'https://example.com/apod.jpg',
            'title' => 'Nebula NGC 1234',
            'explanation' => 'A beautiful nebula in the sky.',
        ]),
    ]);

    Livewire::withoutLazyLoading()
        ->test('holocron.dashboard.components.apod')
        ->assertSuccessful()
        ->assertSee('Nebula NGC 1234');
});

it('renders loading state when api connection fails', function () {
    Http::fake([
        'https://api.nasa.gov/*' => fn () => throw new ConnectionException('Connection refused'),
    ]);

    Livewire::withoutLazyLoading()
        ->test('holocron.dashboard.components.apod')
        ->assertSuccessful()
        ->assertSee('Laden...');
});

it('shows failure message when url is null in response', function () {
    Http::fake([
        'https://api.nasa.gov/*' => Http::response([
            'media_type' => 'video',
        ]),
    ]);

    Livewire::withoutLazyLoading()
        ->test('holocron.dashboard.components.apod')
        ->assertSuccessful()
        ->assertSee('Bildabruf fehlgeschlagen.');
});

it('renders the placeholder without apod data', function () {
    $component = new Modules\Holocron\Dashboard\Livewire\Components\Apod;
    $placeholder = $component->placeholder();

    expect($placeholder->name())->toBe('holocron-dashboard::components.apod');
});
