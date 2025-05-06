<x-layouts.app>
    <x-slot:title>{{ $title ?? '' }}</x-slot>
    <x-slot:header>
        <div class="mx-auto max-w-5xl print:hidden text-sm flex items-center gap-x-6">
            <a
                href="{{ route('holocron.dashboard') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 !border-0 font-mono font-semibold"
            >
                <flux:icon.cpu-chip />
                <span> Holocron </span>
            </a>

            <a
                href="{{ route('holocron.quests') }}"
                wire:navigate
                wire:current="font-semibold"
            >Quests</a>
            <a
                href="{{ route('holocron.grind.workouts.index') }}"
                wire:navigate
                wire:current="font-semibold"
            >Grind</a>
        </div>
    </x-slot>

    <div class="mx-auto mt-6 max-w-5xl">
        {{ $slot }}
    </div>

    <x-slot:footer></x-slot>

    @persist('toast')
        <flux:toast />
    @endpersist
</x-layouts.app>
