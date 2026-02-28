<?php

declare(strict_types=1);

use App\Ai\Tools\LogBodyMeasurement;
use Carbon\CarbonImmutable;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types\IntegerType;
use Illuminate\JsonSchema\Types\NumberType;
use Illuminate\JsonSchema\Types\StringType;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\BodyMeasurement;

use function Pest\Laravel\travelTo;

beforeEach(function () {
    $this->baseDate = CarbonImmutable::create(2099, 6, 15);
    travelTo($this->baseDate);
});

it('logs a new body measurement', function () {
    $tool = new LogBodyMeasurement;

    $result = $tool->handle(new Request([
        'date' => '2099-06-15',
        'weight' => 78.5,
    ]));

    expect($result)->toContain('78.5')
        ->toContain('2099-06-15');

    $measurement = BodyMeasurement::query()->whereDate('date', '2099-06-15')->first();
    expect($measurement)->not->toBeNull()
        ->and((float) $measurement->weight)->toBe(78.50);
});

it('updates an existing measurement for the same date', function () {
    BodyMeasurement::factory()->create([
        'date' => '2099-06-15',
        'weight' => 80.0,
    ]);

    $tool = new LogBodyMeasurement;

    $tool->handle(new Request([
        'date' => '2099-06-15',
        'weight' => 78.5,
    ]));

    expect(BodyMeasurement::query()->whereDate('date', '2099-06-15')->count())->toBe(1);

    $measurement = BodyMeasurement::query()->whereDate('date', '2099-06-15')->first();
    expect((float) $measurement->weight)->toBe(78.50);
});

it('logs optional body composition fields', function () {
    $tool = new LogBodyMeasurement;

    $tool->handle(new Request([
        'date' => '2099-06-15',
        'weight' => 78.5,
        'body_fat' => 18.5,
        'muscle_mass' => 55.2,
        'visceral_fat' => 6,
        'bmi' => 24.1,
        'body_water' => 58.3,
    ]));

    $m = BodyMeasurement::query()->whereDate('date', '2099-06-15')->first();
    expect((float) $m->body_fat)->toBe(18.5)
        ->and((float) $m->muscle_mass)->toBe(55.2)
        ->and($m->visceral_fat)->toBe(6)
        ->and((float) $m->bmi)->toBe(24.1)
        ->and((float) $m->body_water)->toBe(58.3);
});

it('includes delta from previous measurement in response', function () {
    BodyMeasurement::factory()->create([
        'date' => '2099-06-10',
        'weight' => 79.0,
    ]);

    $tool = new LogBodyMeasurement;

    $result = $tool->handle(new Request([
        'date' => '2099-06-15',
        'weight' => 78.5,
    ]));

    expect($result)->toContain('-0.5');
});

it('shows no delta when there is no previous measurement', function () {
    $tool = new LogBodyMeasurement;

    $result = $tool->handle(new Request([
        'date' => '2099-06-15',
        'weight' => 78.5,
    ]));

    expect($result)->not->toContain('Delta since');
});

it('returns the expected schema definition', function () {
    $tool = new LogBodyMeasurement;
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($schema)
        ->toHaveKeys(['date', 'weight', 'body_fat', 'muscle_mass', 'visceral_fat', 'bmi', 'body_water'])
        ->and($schema['date'])->toBeInstanceOf(StringType::class)
        ->and($schema['weight'])->toBeInstanceOf(NumberType::class)
        ->and($schema['visceral_fat'])->toBeInstanceOf(IntegerType::class);

    $object = (new JsonSchemaTypeFactory)->object($schema);
    $serialized = $object->toArray();

    expect($serialized['required'])->toBe(['date', 'weight']);
});
