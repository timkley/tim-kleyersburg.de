<?php

declare(strict_types=1);

use App\Ai\Providers\DirectivesProvider;
use Illuminate\Support\Facades\DB;

it('returns empty string when no directives exist', function () {
    $provider = new DirectivesProvider;

    expect($provider->generate())->toBe('');
});

it('returns formatted directives section when active directives exist', function () {
    DB::table('chopper_directives')->insert([
        ['content' => 'Zeige immer Protein-Ziele beim Loggen', 'created_at' => now(), 'updated_at' => now()],
        ['content' => 'Antworte mit Emojis', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $provider = new DirectivesProvider;
    $result = $provider->generate();

    expect($result)
        ->toContain('Deine gelernten Regeln')
        ->toContain('Zeige immer Protein-Ziele beim Loggen')
        ->toContain('Antworte mit Emojis');
});

it('excludes deactivated directives', function () {
    DB::table('chopper_directives')->insert([
        ['content' => 'Active rule', 'deactivated_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ['content' => 'Inactive rule', 'deactivated_at' => now(), 'created_at' => now(), 'updated_at' => now()],
    ]);

    $provider = new DirectivesProvider;
    $result = $provider->generate();

    expect($result)
        ->toContain('Active rule')
        ->not->toContain('Inactive rule');
});
