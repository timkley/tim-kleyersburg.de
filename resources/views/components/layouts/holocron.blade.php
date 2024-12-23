<x-layouts.app>
    <x-slot:title>{{ $title ?? '' }}</x-slot>
    <x-slot:header>
        <div class="mx-auto max-w-5xl">
            <a
                href="{{ route('holocron.dashboard') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 !border-0 font-mono text-sm font-semibold"
            >
                <x-heroicon-o-cpu-chip class="inline-block h-6 w-6" />
                <span> Holocron </span>
            </a>
        </div>
    </x-slot>

    <div class="mx-auto mt-12 max-w-5xl">
        {{ $slot }}
    </div>

    <x-slot:footer></x-slot>

    @persist('toast')
        <flux:toast />
    @endpersist

    <x-slot:scripts>{{ $scripts ?? '' }}</x-slot>
</x-layouts.app>
