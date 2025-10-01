<?php

declare(strict_types=1);

use Modules\Holocron\Grind\Models\HealthData;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('requires authentication', function () {
    $this->post(route('holocron.grind.health.sync'), [
        'data' => [
            'metrics' => [],
        ],
    ])->assertUnauthorized();
});

it('requires valid bearer token', function () {
    $this->withHeaders([
        'Authorization' => 'Bearer invalid-token',
    ])->post(route('holocron.grind.health.sync'), [
        'data' => [
            'metrics' => [],
        ],
    ])->assertUnauthorized();
});

it('validates required fields', function () {
    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['data']);
});

it('validates metrics structure', function () {
    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), [
        'data' => [
            'metrics' => [
                [
                    'name' => 'test_metric',
                    // missing required fields
                ],
            ],
        ],
    ])->assertStatus(422)
        ->assertJsonValidationErrors([
            'data.metrics.0.units',
            'data.metrics.0.data',
        ]);
});

it('can sync single health metric', function () {
    $payload = [
        'data' => [
            'metrics' => [
                [
                    'name' => 'apple_stand_time',
                    'units' => 'min',
                    'data' => [
                        [
                            'date' => '2025-10-01 00:00:00 +0200',
                            'qty' => 59,
                            'source' => 'Tims Apple Watch',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), $payload);

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Health data synced successfully',
            'processed' => 1,
            'metrics_synced' => 1,
        ]);

    assertDatabaseCount('grind_health_data', 1);
    assertDatabaseHas('grind_health_data', [
        'name' => 'apple_stand_time',
        'units' => 'min',
        'qty' => '59.00',
        'date' => '2025-10-01',
        'source' => 'Tims Apple Watch',
    ]);

    $healthData = HealthData::first();
    expect($healthData->original_payload)->toEqual([
        'date' => '2025-10-01 00:00:00 +0200',
        'qty' => 59,
        'source' => 'Tims Apple Watch',
    ]);
});

it('can sync multiple health metrics', function () {
    $payload = [
        'data' => [
            'metrics' => [
                [
                    'name' => 'apple_stand_time',
                    'units' => 'min',
                    'data' => [
                        [
                            'date' => '2025-10-01 00:00:00 +0200',
                            'qty' => 59,
                            'source' => 'Tims Apple Watch',
                        ],
                    ],
                ],
                [
                    'name' => 'active_energy',
                    'units' => 'kJ',
                    'data' => [
                        [
                            'date' => '2025-10-01 00:00:00 +0200',
                            'qty' => 1215.7114079999997,
                            'source' => 'Tims Apple Watch',
                        ],
                    ],
                ],
                [
                    'name' => 'walking_heart_rate_average',
                    'units' => 'count/min',
                    'data' => [
                        [
                            'date' => '2025-10-01 00:00:00 +0200',
                            'qty' => 105,
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), $payload);

    $response->assertSuccessful()
        ->assertJson([
            'message' => 'Health data synced successfully',
            'processed' => 3,
            'metrics_synced' => 3,
        ]);

    assertDatabaseCount('grind_health_data', 3);

    assertDatabaseHas('grind_health_data', [
        'name' => 'apple_stand_time',
        'units' => 'min',
        'qty' => '59.00',
        'date' => '2025-10-01',
        'source' => 'Tims Apple Watch',
    ]);

    assertDatabaseHas('grind_health_data', [
        'name' => 'active_energy',
        'units' => 'kJ',
        'qty' => '1215.711408', // Full precision as stored
        'date' => '2025-10-01',
        'source' => 'Tims Apple Watch',
    ]);

    assertDatabaseHas('grind_health_data', [
        'name' => 'walking_heart_rate_average',
        'units' => 'count/min',
        'qty' => '105.00',
        'date' => '2025-10-01',
        'source' => null,
    ]);
});

it('can sync multiple data points for same metric', function () {
    $payload = [
        'data' => [
            'metrics' => [
                [
                    'name' => 'apple_stand_time',
                    'units' => 'min',
                    'data' => [
                        [
                            'date' => '2025-10-01 00:00:00 +0200',
                            'qty' => 59,
                            'source' => 'Tims Apple Watch',
                        ],
                        [
                            'date' => '2025-10-02 00:00:00 +0200',
                            'qty' => 67,
                            'source' => 'Tims Apple Watch',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), $payload);

    $response->assertSuccessful()
        ->assertJson([
            'processed' => 2,
            'metrics_synced' => 1,
        ]);

    assertDatabaseCount('grind_health_data', 2);

    assertDatabaseHas('grind_health_data', [
        'name' => 'apple_stand_time',
        'date' => '2025-10-01',
        'qty' => '59.00',
    ]);

    assertDatabaseHas('grind_health_data', [
        'name' => 'apple_stand_time',
        'date' => '2025-10-02',
        'qty' => '67.00',
    ]);
});

it('handles deduplication correctly', function () {
    // First sync
    $payload = [
        'data' => [
            'metrics' => [
                [
                    'name' => 'apple_stand_time',
                    'units' => 'min',
                    'data' => [
                        [
                            'date' => '2025-10-01 00:00:00 +0200',
                            'qty' => 59,
                            'source' => 'Tims Apple Watch',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), $payload)
        ->assertSuccessful();

    assertDatabaseCount('grind_health_data', 1);

    // Second sync with updated data for same date/metric
    $updatedPayload = [
        'data' => [
            'metrics' => [
                [
                    'name' => 'apple_stand_time',
                    'units' => 'min',
                    'data' => [
                        [
                            'date' => '2025-10-01 00:00:00 +0200',
                            'qty' => 67, // Updated quantity
                            'source' => 'Tims Apple Watch',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), $updatedPayload)
        ->assertSuccessful();

    // Should still have only 1 record, but with updated data
    assertDatabaseCount('grind_health_data', 1);

    assertDatabaseHas('grind_health_data', [
        'name' => 'apple_stand_time',
        'date' => '2025-10-01',
        'qty' => '67.00', // Updated value
    ]);
});

it('strips time from dates correctly', function () {
    $payload = [
        'data' => [
            'metrics' => [
                [
                    'name' => 'test_metric',
                    'units' => 'count',
                    'data' => [
                        [
                            'date' => '2025-10-01 15:30:45 +0200',
                            'qty' => 100,
                        ],
                    ],
                ],
            ],
        ],
    ];

    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), $payload)
        ->assertSuccessful();

    assertDatabaseHas('grind_health_data', [
        'name' => 'test_metric',
        'date' => '2025-10-01', // Time stripped, only date remains
        'qty' => '100.00',
    ]);
});

it('handles malformed json gracefully', function () {
    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
        'Content-Type' => 'application/json',
    ])->postJson(route('holocron.grind.health.sync'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['data']);
});

it('preserves original payload for debugging', function () {
    $originalDataPoint = [
        'date' => '2025-10-01 15:30:45 +0200',
        'qty' => 123.456789,
        'source' => 'Test Device',
    ];

    $payload = [
        'data' => [
            'metrics' => [
                [
                    'name' => 'test_metric',
                    'units' => 'count',
                    'data' => [$originalDataPoint],
                ],
            ],
        ],
    ];

    $this->withHeaders([
        'Authorization' => 'Bearer '.config('auth.bearer_token'),
    ])->post(route('holocron.grind.health.sync'), $payload)
        ->assertSuccessful();

    $healthData = HealthData::first();
    // Only validated fields are preserved in original_payload
    expect($healthData->original_payload)->toEqual($originalDataPoint);
});
