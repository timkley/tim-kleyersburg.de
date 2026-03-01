<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Modules\Holocron\Quest\Models\Quest;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Http::fake([
        'geocoding-api.open-meteo.com/*' => Http::response([
            'results' => [['latitude' => 48.8, 'longitude' => 9.27]],
        ]),
        'api.open-meteo.com/*' => Http::response([
            'daily' => [
                'time' => [today()->format('Y-m-d')],
                'weather_code' => [0],
                'temperature_2m_max' => [20.0],
                'temperature_2m_min' => [10.0],
                'precipitation_sum' => [0.0],
            ],
        ]),
    ]);

    $user = User::factory()
        ->has(UserSetting::factory(), 'settings')
        ->create(['email' => 'timkley@gmail.com']);

    actingAs($user);
});

it('is not reachable when unauthenticated', function () {
    auth()->logout();

    get(route('holocron.dashboard'))
        ->assertRedirect();
});

it('renders the dashboard page', function () {
    Livewire::test('holocron.dashboard.index')
        ->assertSuccessful();
});

it('passes todays quests to the view', function () {
    Quest::factory()->create([
        'date' => today(),
        'daily' => false,
        'completed_at' => null,
    ]);

    Quest::factory()->create([
        'date' => today(),
        'daily' => false,
        'completed_at' => now(),
    ]);

    $component = Livewire::test('holocron.dashboard.index');

    $todaysQuests = $component->viewData('todaysQuests');

    expect($todaysQuests)->toHaveCount(1)
        ->and($todaysQuests->first()->completed_at)->toBeNull();
});

it('does not show quests with future dates', function () {
    Quest::factory()->create([
        'date' => today()->addDay(),
        'daily' => false,
        'completed_at' => null,
    ]);

    $component = Livewire::test('holocron.dashboard.index');

    $todaysQuests = $component->viewData('todaysQuests');

    expect($todaysQuests)->toHaveCount(0);
});

it('shows quests with past dates that are not completed', function () {
    Quest::factory()->create([
        'date' => today()->subDay(),
        'daily' => false,
        'completed_at' => null,
    ]);

    $component = Livewire::test('holocron.dashboard.index');

    $todaysQuests = $component->viewData('todaysQuests');

    expect($todaysQuests)->toHaveCount(1);
});
