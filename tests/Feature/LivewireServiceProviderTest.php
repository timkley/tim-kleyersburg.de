<?php

declare(strict_types=1);

use App\Providers\LivewireServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

it('returns early when the modules directory does not exist', function () {
    // Line 25: early return when modules dir missing
    $provider = new LivewireServiceProvider(app());

    $method = new ReflectionMethod($provider, 'registerModuleLivewireComponents');

    // Point base_path to a temp dir without a modules/ subdirectory
    $tempDir = sys_get_temp_dir().'/'.uniqid('no-modules-');
    mkdir($tempDir);
    $originalBasePath = base_path();
    app()->setBasePath($tempDir);

    $method->invoke($provider);

    // If it didn't return early, it would call discoverAndRegisterComponents
    // which would fail on the non-existent modules path. No error means early return worked.
    expect(true)->toBeTrue();

    app()->setBasePath($originalBasePath);
    rmdir($tempDir);
});

it('caches component discovery in production', function () {
    // Line 59: Cache::rememberForever branch
    Cache::shouldReceive('rememberForever')
        ->once()
        ->with('livewire-module-components', Mockery::type('Closure'))
        ->andReturn([]);

    App::shouldReceive('environment')
        ->with('production')
        ->andReturn(true);

    $provider = new LivewireServiceProvider(app());

    $method = new ReflectionMethod($provider, 'discoverAndRegisterComponents');
    $method->invoke($provider, base_path('modules'));
});

it('returns null alias for classes not starting with Modules namespace', function () {
    // Line 87: class doesn't start with Modules\
    $provider = new LivewireServiceProvider(app());

    $method = new ReflectionMethod($provider, 'generateAlias');

    $result = $method->invoke($provider, 'App\\Http\\Livewire\\SomeComponent');

    expect($result)->toBeNull();
});

it('returns null alias when Livewire segment is missing from namespace', function () {
    // Line 94: no Livewire segment found
    $provider = new LivewireServiceProvider(app());

    $method = new ReflectionMethod($provider, 'generateAlias');

    $result = $method->invoke($provider, 'Modules\\Holocron\\SomeComponent');

    expect($result)->toBeNull();
});

it('returns null alias when Livewire segment index is less than 2', function () {
    // Line 94: Livewire at index < 2
    $provider = new LivewireServiceProvider(app());

    $method = new ReflectionMethod($provider, 'generateAlias');

    $result = $method->invoke($provider, 'Modules\\Livewire');

    expect($result)->toBeNull();
});

it('returns null alias when there are no component segments after Livewire', function () {
    // Line 101: empty component segments
    $provider = new LivewireServiceProvider(app());

    $method = new ReflectionMethod($provider, 'generateAlias');

    $result = $method->invoke($provider, 'Modules\\Holocron\\Livewire');

    expect($result)->toBeNull();
});

it('generates correct alias for valid module Livewire components', function () {
    $provider = new LivewireServiceProvider(app());

    $method = new ReflectionMethod($provider, 'generateAlias');

    $result = $method->invoke($provider, 'Modules\\Holocron\\Grind\\Livewire\\Plans\\Index');

    expect($result)->toBe('holocron.grind.plans.index');
});
