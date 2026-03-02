<?php

declare(strict_types=1);

use App\Ai\Tools\EvalTool;
use Laravel\Ai\Tools\Request;

it('executes simple PHP code and returns output', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return 2 + 2;',
    ]));

    expect($result)->toContain('4');
});

it('allows Carbon usage', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return \Carbon\Carbon::today()->toDateString();',
    ]));

    expect($result)->toContain(today()->toDateString());
});

it('allows model queries', function () {
    Modules\Holocron\Grind\Models\NutritionDay::factory()->create(['date' => '2099-06-01']);

    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return \Modules\Holocron\Grind\Models\NutritionDay::query()->whereDate("date", "2099-06-01")->exists();',
    ]));

    expect($result)->toContain('true');
});

it('blocks filesystem operations', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'file_get_contents("/etc/passwd");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks exec calls', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'exec("whoami");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks shell_exec calls', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'shell_exec("ls");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks Storage facade', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '\Illuminate\Support\Facades\Storage::get("test");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks File facade', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '\Illuminate\Support\Facades\File::get("/etc/passwd");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks Process facade', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '\Illuminate\Support\Facades\Process::run("ls");',
    ]));

    expect($result)->toContain('not allowed');
});

it('allows Http facade', function () {
    Illuminate\Support\Facades\Http::fake(['*' => Illuminate\Support\Facades\Http::response('ok')]);

    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return \Illuminate\Support\Facades\Http::get("https://example.com")->body();',
    ]));

    expect($result)->toContain('ok');
});

it('allows collection methods', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return collect([1, 2, 3])->sum();',
    ]));

    expect($result)->toContain('6');
});

it('blocks call_user_func bypass', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'call_user_func("exec", "whoami");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks array_map callable bypass', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'array_map("system", ["whoami"]);',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks backtick operator', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '`whoami`;',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks ReflectionFunction bypass', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '(new ReflectionFunction("exec"))->invoke("whoami");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks variable function calls', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => '$fn = "exec"; $fn("whoami");',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks chr construction of function names', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'chr(101).chr(120).chr(101).chr(99);',
    ]));

    expect($result)->toContain('not allowed');
});

it('blocks short facade names used as static calls', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'Storage::get("test");',
    ]));

    expect($result)->toContain('not allowed');
});

it('does not false-positive on variable names containing blocked substrings', function () {
    $tool = new EvalTool;

    $result = (string) $tool->handle(new Request([
        'code' => 'return "ProfileStorage";',
    ]));

    expect($result)->not->toContain('not allowed')
        ->and($result)->toContain('ProfileStorage');
});

it('returns the expected schema definition', function () {
    $tool = new EvalTool;

    $schema = $tool->schema(new Illuminate\JsonSchema\JsonSchemaTypeFactory);

    expect($schema)->toHaveKey('code')
        ->and($schema['code'])->toBeInstanceOf(Illuminate\JsonSchema\Types\StringType::class);
});
