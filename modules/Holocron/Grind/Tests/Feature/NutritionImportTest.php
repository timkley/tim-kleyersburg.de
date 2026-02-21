<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\BodyMeasurement;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

beforeEach(function () {
    $user = User::factory()->create();
    UserSetting::factory()->create(['user_id' => $user->id]);

    // Re-run the import migration so the user and settings exist
    (include base_path('modules/Holocron/Grind/Database/Migrations/2026_02_21_210019_import_nutrition_json_data.php'))->up();
});

it('imports daily targets to user settings', function () {
    $settings = UserSetting::first();
    expect($settings->nutrition_daily_targets)->toBeArray()
        ->and($settings->nutrition_daily_targets['training']['kcal'])->toBe(2200)
        ->and($settings->nutrition_daily_targets['rest']['protein'])->toBe(155)
        ->and($settings->nutrition_daily_targets['sick']['carbs'])->toBe(250);
});

it('imports nutrition days from json', function () {
    expect(NutritionDay::count())->toBe(20);

    $feb01 = NutritionDay::query()->whereDate('date', '2026-02-01')->first();
    expect($feb01)->not->toBeNull()
        ->and($feb01->type)->toBe('training')
        ->and($feb01->training_label)->toBe('lower')
        ->and($feb01->meals)->toBeArray()
        ->and(count($feb01->meals))->toBeGreaterThan(0)
        ->and($feb01->total_kcal)->toBe(2380)
        ->and($feb01->notes)->toBe('Tag 1 Tracking. Pizza proteinschwach, Lachsnudeln zum Ausgleich.');
});

it('imports body measurements from json', function () {
    expect(BodyMeasurement::count())->toBe(3);

    $firstMeasurement = BodyMeasurement::query()->whereDate('date', '2026-01-19')->first();
    expect($firstMeasurement)->not->toBeNull()
        ->and((float) $firstMeasurement->weight)->toBe(77.55)
        ->and((float) $firstMeasurement->body_fat)->toBe(22.2)
        ->and((float) $firstMeasurement->muscle_mass)->toBe(57.3);

    $lastMeasurement = BodyMeasurement::query()->whereDate('date', '2026-02-21')->first();
    expect($lastMeasurement)->not->toBeNull()
        ->and((float) $lastMeasurement->bmi)->toBe(22.7)
        ->and((float) $lastMeasurement->body_water)->toBe(56.8);
});

it('imports days at root level of json alongside nested days', function () {
    $feb19 = NutritionDay::query()->whereDate('date', '2026-02-19')->first();
    expect($feb19)->not->toBeNull()
        ->and($feb19->type)->toBe('rest')
        ->and($feb19->total_kcal)->toBe(2078);

    $feb20 = NutritionDay::query()->whereDate('date', '2026-02-20')->first();
    expect($feb20)->not->toBeNull()
        ->and($feb20->type)->toBe('training')
        ->and($feb20->training_label)->toBe('upper');
});

it('is idempotent when run multiple times', function () {
    // The migration already ran in beforeEach, run it again
    (include base_path('modules/Holocron/Grind/Database/Migrations/2026_02_21_210019_import_nutrition_json_data.php'))->up();

    expect(NutritionDay::count())->toBe(20)
        ->and(BodyMeasurement::count())->toBe(3);
});
