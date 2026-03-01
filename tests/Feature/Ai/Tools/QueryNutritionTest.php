<?php

declare(strict_types=1);

use App\Ai\Tools\QueryNutrition;
use Carbon\CarbonImmutable;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types\StringType;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\NutritionDay;
use Modules\Holocron\User\Models\User;
use Modules\Holocron\User\Models\UserSetting;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\travelTo;

// Use a far-future date to avoid collisions with data imported by the migration seeder.
beforeEach(function () {
    $this->baseDate = CarbonImmutable::create(2099, 6, 15);
    travelTo($this->baseDate);

    $user = User::factory()->has(UserSetting::factory()->state([
        'nutrition_daily_targets' => [
            'training' => ['kcal' => 2200, 'protein' => 155, 'fat' => 65, 'carbs' => 230],
            'rest' => ['kcal' => 2000, 'protein' => 155, 'fat' => 60, 'carbs' => 185],
        ],
    ]), 'settings')->create();

    actingAs($user);
});

it('returns today data with meals and targets', function () {
    NutritionDay::factory()->create([
        'date' => $this->baseDate,
        'type' => 'training',
        'training_label' => 'upper',
        'meals' => [['name' => 'Shake', 'time' => '08:00', 'kcal' => 110, 'protein' => 23, 'fat' => 0, 'carbs' => 3]],
        'total_kcal' => 110,
        'total_protein' => 23,
        'total_fat' => 0,
        'total_carbs' => 3,
    ]);

    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'today']));

    expect($result)
        ->toContain('Shake')
        ->toContain('110 kcal')
        ->toContain('training')
        ->toContain('upper')
        ->toContain('Targets');
});

it('returns no data message for empty today', function () {
    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'today']));

    expect($result)->toContain('No nutrition data');
});

it('returns data for a specific date', function () {
    $specificDate = $this->baseDate->subDays(2);

    NutritionDay::factory()->create([
        'date' => $specificDate,
        'type' => 'rest',
        'meals' => [['name' => 'Lunch', 'kcal' => 700, 'protein' => 40, 'fat' => 25, 'carbs' => 70]],
        'total_kcal' => 700,
        'total_protein' => 40,
        'total_fat' => 25,
        'total_carbs' => 70,
    ]);

    $tool = new QueryNutrition;
    $result = $tool->handle(new Request([
        'query_type' => 'date',
        'date' => $specificDate->toDateString(),
    ]));

    expect($result)
        ->toContain('Lunch')
        ->toContain('700 kcal')
        ->toContain('rest');
});

it('returns no data message for empty specific date', function () {
    $tool = new QueryNutrition;
    $result = $tool->handle(new Request([
        'query_type' => 'date',
        'date' => '2099-01-01',
    ]));

    expect($result)->toContain('No nutrition data for 2099-01-01');
});

it('returns weekly summary', function () {
    for ($i = 0; $i < 3; $i++) {
        NutritionDay::factory()->create([
            'date' => $this->baseDate->subDays($i),
            'type' => 'training',
            'total_kcal' => 2000 + ($i * 100),
        ]);
    }

    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'week']));

    expect($result)
        ->toContain('2000')
        ->toContain('2100')
        ->toContain('2200');
});

it('returns no data message for empty week', function () {
    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'week']));

    expect($result)->toContain('No nutrition data for the last 7 days');
});

it('returns 7-day averages with targets', function () {
    for ($i = 0; $i < 7; $i++) {
        NutritionDay::factory()->create([
            'date' => $this->baseDate->subDays($i),
            'type' => 'training',
            'total_kcal' => 2100,
            'total_protein' => 150,
            'total_fat' => 60,
            'total_carbs' => 220,
        ]);
    }

    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'average']));

    expect($result)
        ->toContain('7-day average (7 days)')
        ->toContain('2100 kcal')
        ->toContain('150g protein')
        ->toContain('target');
});

it('returns no data message for empty average', function () {
    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'average']));

    expect($result)->toContain('No nutrition data for the last 7 days to average');
});

it('returns error for unknown query type', function () {
    $tool = new QueryNutrition;
    $result = $tool->handle(new Request(['query_type' => 'invalid']));

    expect($result)->toContain('Unknown query type');
});

it('returns schema with query_type and date properties', function () {
    $tool = new QueryNutrition;
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($schema)
        ->toHaveKeys(['query_type', 'date'])
        ->and($schema['query_type'])->toBeInstanceOf(StringType::class)
        ->and($schema['date'])->toBeInstanceOf(StringType::class);

    // Wrap in an ObjectType to verify that query_type is required and date is not.
    $object = (new JsonSchemaTypeFactory)->object($schema);
    $serialized = $object->toArray();

    expect($serialized['required'])->toBe(['query_type']);
});
