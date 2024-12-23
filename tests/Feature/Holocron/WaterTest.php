<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\get;

it('is not reachable when unauthenticated', function () {
    get(route('holocron.water'))
        ->assertRedirect(route('holocron.login'));
});

it('knows the goal', function ($weight, $temperature, $expected) {
    $user = User::factory()->create();
    $user->settings()->create([
        'weight' => $weight,
    ]);

    Http::fake([
        'https://api.weatherapi.com/*' => Http::response([
            'forecast' => [
                'forecastday' => [
                    [
                        'day' => [
                            'maxtemp_c' => $temperature,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    Livewire::actingAs($user)
        ->test('holocron.water')
        ->assertViewHas('goal', $expected);
})->with([
    [
        70,
        20,
        2310,
    ],
    [
        70,
        25,
        2810,
    ],
    [
        70,
        30,
        3060,
    ],
]);
