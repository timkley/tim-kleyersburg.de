<?php

declare(strict_types=1);

use App\Models\Holocron\Health\Intake;

it('creates an intake model', function () {
    $intake = Intake::factory()->create([
        'type' => 'water',
        'unit' => 'ml',
    ]);

    expect($intake->exists)->toBeTrue();
    expect($intake->type->value)->toBe('water');
    expect($intake->unit->value)->toBe('ml');
});
