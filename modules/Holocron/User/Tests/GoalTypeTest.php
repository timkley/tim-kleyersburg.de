<?php

declare(strict_types=1);

use Modules\Holocron\User\Enums\GoalType;
use Modules\Holocron\User\Enums\GoalUnit;

it('maps each goal type to the correct unit', function (GoalType $type, GoalUnit $expectedUnit) {
    expect($type->unit())->toBe($expectedUnit);
})->with([
    [GoalType::Water, GoalUnit::Milliliters],
    [GoalType::Creatine, GoalUnit::Grams],
    [GoalType::Planks, GoalUnit::Seconds],
    [GoalType::Mobility, GoalUnit::Boolean],
    [GoalType::NoSmoking, GoalUnit::Boolean],
    [GoalType::NoAlcohol, GoalUnit::Boolean],
    [GoalType::Protein, GoalUnit::Grams],
]);

it('marks planks and creatine as deactivated', function () {
    expect(GoalType::Planks->deactivated())->toBeTrue();
    expect(GoalType::Creatine->deactivated())->toBeTrue();
});

it('marks other goal types as active', function (GoalType $type) {
    expect($type->deactivated())->toBeFalse();
})->with([
    GoalType::Water,
    GoalType::Mobility,
    GoalType::NoSmoking,
    GoalType::NoAlcohol,
    GoalType::Protein,
]);

it('returns correct default amounts', function (GoalType $type, int $expectedAmount) {
    expect($type->defaultAmount())->toBe($expectedAmount);
})->with([
    [GoalType::NoSmoking, 1],
    [GoalType::NoAlcohol, 1],
    [GoalType::Water, 0],
    [GoalType::Creatine, 0],
    [GoalType::Planks, 0],
    [GoalType::Mobility, 0],
    [GoalType::Protein, 0],
]);

it('has the correct backing values', function (GoalType $type, string $expectedValue) {
    expect($type->value)->toBe($expectedValue);
})->with([
    [GoalType::Water, 'water'],
    [GoalType::Creatine, 'creatine'],
    [GoalType::Planks, 'planks'],
    [GoalType::Mobility, 'mobility'],
    [GoalType::NoSmoking, 'no_smoking'],
    [GoalType::NoAlcohol, 'no_alcohol'],
    [GoalType::Protein, 'protein'],
]);

it('has all expected cases', function () {
    expect(GoalType::cases())->toHaveCount(7);
});

it('calculates protein goal based on weight', function () {
    $user = Modules\Holocron\User\Models\User::factory()->create(['email' => 'timkley@gmail.com']);
    $user->settings()->create(['weight' => 80]);

    expect(GoalType::Protein->goal())->toBe(160);
});

it('returns fixed goal for creatine', function () {
    expect(GoalType::Creatine->goal())->toBe(5);
});

it('returns fixed goal for mobility', function () {
    expect(GoalType::Mobility->goal())->toBe(1);
});

it('returns fixed goal for no smoking', function () {
    expect(GoalType::NoSmoking->goal())->toBe(1);
});

it('returns fixed goal for no alcohol', function () {
    expect(GoalType::NoAlcohol->goal())->toBe(1);
});
