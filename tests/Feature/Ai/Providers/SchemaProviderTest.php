<?php

declare(strict_types=1);

use App\Ai\Providers\SchemaProvider;

it('generates a compact schema summary string', function () {
    $provider = new SchemaProvider;

    $schema = $provider->generate();

    expect($schema)
        ->toContain('grind_nutrition_days')
        ->toContain('grind_body_measurements')
        ->toContain('grind_meals')
        ->toContain('quests')
        ->toContain('quest_notes')
        ->toContain('agent_conversation_messages')
        ->not->toContain('migrations')
        ->not->toContain('password_reset_tokens');
});

it('includes column names for each table', function () {
    $provider = new SchemaProvider;

    $schema = $provider->generate();

    expect($schema)
        ->toContain('date')
        ->toContain('weight')
        ->toContain('name');
});
