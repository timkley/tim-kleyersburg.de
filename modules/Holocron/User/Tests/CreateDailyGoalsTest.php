<?php

declare(strict_types=1);

use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Jobs\CreateDailyGoals;
use Modules\Holocron\User\Models\DailyGoal;
use Modules\Holocron\User\Models\User;

beforeEach(function () {
    Http::fake([
        'https://geocoding-api.open-meteo.com/v1/search?name=Fellbach&count=1&language=de&format=json' => Http::response([]),
    ]);

    User::factory()->create(['email' => 'timkley@gmail.com']);
});

it('creates daily goals for all active goal types', function () {
    (new CreateDailyGoals)->handle();

    $activeTypes = collect(GoalType::cases())
        ->reject(fn (GoalType $type) => $type->deactivated());

    expect(DailyGoal::count())->toBe($activeTypes->count());

    foreach ($activeTypes as $type) {
        expect(DailyGoal::where('type', $type)->exists())->toBeTrue();
    }
});

it('does not create goals for deactivated types', function () {
    (new CreateDailyGoals)->handle();

    $deactivatedTypes = collect(GoalType::cases())
        ->filter(fn (GoalType $type) => $type->deactivated());

    foreach ($deactivatedTypes as $type) {
        expect(DailyGoal::where('type', $type)->exists())->toBeFalse();
    }
});

it('does not duplicate goals when run twice', function () {
    (new CreateDailyGoals)->handle();
    $countAfterFirst = DailyGoal::count();

    (new CreateDailyGoals)->handle();
    $countAfterSecond = DailyGoal::count();

    expect($countAfterFirst)->toBe($countAfterSecond);
});

it('implements ShouldQueue', function () {
    expect(new CreateDailyGoals)->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
});
