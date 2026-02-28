<?php

declare(strict_types=1);

use App\Ai\Tools\QueryBodyMeasurements;
use Carbon\CarbonImmutable;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\JsonSchema\Types\StringType;
use Laravel\Ai\Tools\Request;
use Modules\Holocron\Grind\Models\BodyMeasurement;

use function Pest\Laravel\travelTo;

beforeEach(function () {
    $this->baseDate = CarbonImmutable::create(2099, 6, 15);
    travelTo($this->baseDate);
});

it('returns the latest measurement', function () {
    BodyMeasurement::factory()->create(['date' => '2099-06-10', 'weight' => 79.0]);
    BodyMeasurement::factory()->create(['date' => '2099-06-15', 'weight' => 78.5, 'body_fat' => 18.5]);

    $tool = new QueryBodyMeasurements;
    $result = $tool->handle(new Request(['query_type' => 'latest']));

    expect($result)
        ->toContain('78.5')
        ->toContain('18.5')
        ->toContain('2099-06-15');
});

it('returns no data message for latest when empty', function () {
    $tool = new QueryBodyMeasurements;
    $result = $tool->handle(new Request(['query_type' => 'latest']));

    expect($result)->toContain('No body measurements');
});

it('returns measurement for a specific date', function () {
    BodyMeasurement::factory()->create(['date' => '2099-06-10', 'weight' => 79.0]);

    $tool = new QueryBodyMeasurements;
    $result = $tool->handle(new Request([
        'query_type' => 'date',
        'date' => '2099-06-10',
    ]));

    expect($result)
        ->toContain('79.0')
        ->toContain('2099-06-10');
});

it('returns no data message for empty date', function () {
    $tool = new QueryBodyMeasurements;
    $result = $tool->handle(new Request([
        'query_type' => 'date',
        'date' => '2099-01-01',
    ]));

    expect($result)->toContain('No body measurement for 2099-01-01');
});

it('returns trend with deltas for last 7 measurements', function () {
    for ($i = 6; $i >= 0; $i--) {
        BodyMeasurement::factory()->create([
            'date' => $this->baseDate->subDays($i),
            'weight' => 80.0 - ($i * 0.2),
        ]);
    }

    $tool = new QueryBodyMeasurements;
    $result = $tool->handle(new Request(['query_type' => 'trend']));

    expect($result)
        ->toContain('2099-06-09')
        ->toContain('2099-06-15')
        ->toContain('80.0');
});

it('returns no data message for empty trend', function () {
    $tool = new QueryBodyMeasurements;
    $result = $tool->handle(new Request(['query_type' => 'trend']));

    expect($result)->toContain('No body measurements');
});

it('returns error for unknown query type', function () {
    $tool = new QueryBodyMeasurements;
    $result = $tool->handle(new Request(['query_type' => 'invalid']));

    expect($result)->toContain('Unknown query type');
});

it('returns schema with query_type and date properties', function () {
    $tool = new QueryBodyMeasurements;
    $schema = $tool->schema(new JsonSchemaTypeFactory);

    expect($schema)
        ->toHaveKeys(['query_type', 'date'])
        ->and($schema['query_type'])->toBeInstanceOf(StringType::class)
        ->and($schema['date'])->toBeInstanceOf(StringType::class);

    $object = (new JsonSchemaTypeFactory)->object($schema);
    $serialized = $object->toArray();

    expect($serialized['required'])->toBe(['query_type']);
});
