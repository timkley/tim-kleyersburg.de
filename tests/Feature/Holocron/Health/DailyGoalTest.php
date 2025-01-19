<?php

declare(strict_types=1);

use App\Enums\Holocron\Health\IntakeTypes;
use App\Models\Holocron\Health\DailyGoal;
use App\Models\User;

it('gets the daily goal for each type', function (IntakeTypes $type, int $amount, ?int $weight = null, ?int $temperature = null) {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $user->settings()->create([
        'weight' => $weight,
    ]);

    Http::fake([
        'https://api.weatherapi.com/*' => Http::response([
            'forecast' => [
                'forecastday' => [
                    [
                        'day' => [
                            'maxtemp_c' => $temperature ?? 20,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    expect(DailyGoal::for($type)->goal)->toBe($amount);
    expect(DailyGoal::count())->toBe(1);

    // we double-check to make sure that the goal is only created once
    expect(DailyGoal::for($type)->goal)->toBe($amount);
    expect(DailyGoal::count())->toBe(1);
})->with([
    [
        IntakeTypes::Water,
        2310,
        70,
        20,
    ],
    [
        IntakeTypes::Water,
        2975,
        75,
        25,
    ],
    [
        IntakeTypes::Water,
        3060,
        70,
        30,
    ],
    [
        IntakeTypes::Creatine,
        5,
    ],
    [
        IntakeTypes::Planks,
        90,
    ],
]);

it('gets a progressive goal for planks', function () {
    expect(DailyGoal::for(IntakeTypes::Planks)->goal)->toBe(90);

    \Pest\Laravel\travel(5)->days();
    DailyGoal::factory()->create([
        'type' => IntakeTypes::Planks,
        'goal' => 90,
        'amount' => 90,
    ]);

    expect(DailyGoal::for(IntakeTypes::Planks)->goal)->toBe(95);
});
