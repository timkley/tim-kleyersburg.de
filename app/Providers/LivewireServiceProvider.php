<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;

class LivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerModuleLivewireComponents();
    }

    protected function registerModuleLivewireComponents(): void
    {
        $modulesPath = base_path('modules');
        if (! is_dir($modulesPath)) {
            return;
        }

        $this->discoverAndRegisterComponents($modulesPath);
    }

    private function discoverAndRegisterComponents(string $path): void
    {
        $discover = function () use ($path) {
            $finder = (new Finder)
                ->in($path)
                ->name('*.php')
                ->exclude('Tests')
                ->files();

            $components = [];
            foreach ($finder as $file) {
                $class = $this->getClassFromFile($file->getRealPath());

                if (! $class || ! is_subclass_of($class, \Livewire\Component::class)) {
                    continue;
                }

                $alias = $this->generateAlias($class);

                if ($alias) {
                    $components[$alias] = $class;
                }
            }

            return $components;
        };

        $components = App::environment('production')
            ? Cache::rememberForever('livewire-module-components', $discover)
            : $discover();

        foreach ($components as $alias => $class) {
            Livewire::component($alias, $class);
        }
    }

    private function getClassFromFile(string $path): ?string
    {
        $class = Str::of($path)
            ->replaceFirst(base_path(), '')
            ->ltrim(DIRECTORY_SEPARATOR)
            ->replace(DIRECTORY_SEPARATOR, '\\')
            ->ucfirst()
            ->replaceLast('.php', '')
            ->toString();

        if (! class_exists($class)) {
            return null;
        }

        return $class;
    }

    private function generateAlias(string $class): ?string
    {
        if (! Str::startsWith($class, 'Modules\\')) {
            return null;
        }

        $parts = explode('\\', $class);
        $livewireIndex = array_search('Livewire', $parts, true);

        if ($livewireIndex === false || $livewireIndex < 2) {
            return null;
        }

        $moduleSegments = array_slice($parts, 1, $livewireIndex - 1);
        $componentSegments = array_slice($parts, $livewireIndex + 1);

        if (empty($componentSegments)) {
            return null;
        }

        $aliasParts = collect()
            ->merge($moduleSegments)
            ->merge($componentSegments)
            ->map(fn ($part) => Str::of($part)->replace('_', '')->kebab()->lower())
            ->implode('.');

        return (string) $aliasParts;
    }
}
