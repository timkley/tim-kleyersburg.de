<?php

declare(strict_types=1);

use App\Enums\Holocron\Health\GoalType;
use App\Models\Holocron\Health\DailyGoal;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\travel;

beforeEach(function () {
    Http::fake([
        'https://geocoding-api.open-meteo.com/v1/search?name=Fellbach&count=1&language=de&format=json' => Http::response([]),
    ]);
});

it('gets the daily goal for each type', function (GoalType $type, int $amount, ?int $weight = null, ?int $temperature = null) {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);
    $user->settings()->create([
        'weight' => $weight,
    ]);

    expect(DailyGoal::for($type)->goal)->toBe($amount);
    expect(DailyGoal::count())->toBe(1);

    // we double-check to make sure that the goal is only created once
    expect(DailyGoal::for($type)->goal)->toBe($amount);
    expect(DailyGoal::count())->toBe(1);
})->with([
    [
        GoalType::Water,
        2310,
        70,
        20,
    ],
    [
        GoalType::Water,
        2475,
        75,
        25,
    ],
    [
        GoalType::Water,
        2310,
        70,
        30,
    ],
    [
        GoalType::Creatine,
        5,
    ],
    [
        GoalType::Planks,
        90,
    ],
]);

it('gets a progressive goal for planks', function () {
    expect(DailyGoal::for(GoalType::Planks)->goal)->toBe(90);

    travel(5)->days();
    DailyGoal::factory()->create([
        'type' => GoalType::Planks,
        'goal' => 90,
        'amount' => 90,
    ]);

    expect(DailyGoal::for(GoalType::Planks)->goal)->toBe(95);
});

it('can track a goal', function () {
    $user = User::factory()->create(['email' => 'timkley@gmail.com']);

    actingAs($user);
    Livewire::test('holocron.dashboard.components.goals')
        ->call('trackGoal', GoalType::Water->value, 1000)
        ->call('trackGoal', GoalType::NoSmoking->value, -1);

    expect(DailyGoal::for(GoalType::Water)->amount)->toBe(1000);
    expect(DailyGoal::for(GoalType::NoSmoking)->amount)->toBe(0);
});

it('adds experience for reached and unreached goals', function () {
    $user = User::factory(['email' => 'timkley@gmail.com'])->create();
    actingAs($user);
    // creating a water goal awards no xp
    // creating a smoking goal awards xp

    $water = DailyGoal::for(GoalType::Water);
    expect($user->fresh()->experienceLogs->count())->toBe(0);

    $smoke = DailyGoal::for(GoalType::NoSmoking);
    expect($user->fresh()->experienceLogs->count())->toBe(1);

    $water->track(3000);
    expect($user->fresh()->experienceLogs->count())->toBe(2);

    $smoke->track(-1);
    expect($user->fresh()->experienceLogs->count())->toBe(3);
});
