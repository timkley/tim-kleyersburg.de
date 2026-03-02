<?php

declare(strict_types=1);

use App\Ai\Agents\ChopperAgent;
use App\Ai\Tools\DatabaseTool;
use App\Ai\Tools\EvalTool;
use App\Ai\Tools\FilesystemTool;
use Illuminate\Support\Facades\DB;

it('registers exactly three tools', function () {
    $agent = new ChopperAgent;
    $tools = iterator_to_array($agent->tools());

    expect($tools)->toHaveCount(3);
});

it('registers DatabaseTool, EvalTool, and FilesystemTool', function () {
    $agent = new ChopperAgent;
    $tools = iterator_to_array($agent->tools());

    $toolClasses = array_map(fn ($tool) => $tool::class, $tools);

    expect($toolClasses)->toContain(DatabaseTool::class)
        ->toContain(EvalTool::class)
        ->toContain(FilesystemTool::class);
});

it('includes schema summary in instructions', function () {
    $agent = new ChopperAgent;
    $instructions = (string) $agent->instructions();

    expect($instructions)
        ->toContain('Database Schema')
        ->toContain('grind_nutrition_days')
        ->toContain('quests');
});

it('includes tool selection guidance in instructions', function () {
    $agent = new ChopperAgent;
    $instructions = (string) $agent->instructions();

    expect($instructions)
        ->toContain('DatabaseTool')
        ->toContain('EvalTool')
        ->toContain('FilesystemTool');
});

it('includes active directives in instructions', function () {
    DB::table('chopper_directives')->insert([
        ['content' => 'Zeige immer Protein-Ziele', 'created_at' => now(), 'updated_at' => now()],
    ]);

    $agent = new ChopperAgent;
    $instructions = (string) $agent->instructions();

    expect($instructions)
        ->toContain('Deine gelernten Regeln')
        ->toContain('Zeige immer Protein-Ziele');
});

it('omits directives section when no directives exist', function () {
    $agent = new ChopperAgent;
    $instructions = (string) $agent->instructions();

    expect($instructions)->not->toContain('Deine gelernten Regeln');
});

it('includes directive management instructions', function () {
    $agent = new ChopperAgent;
    $instructions = (string) $agent->instructions();

    expect($instructions)->toContain('chopper_directives');
});
