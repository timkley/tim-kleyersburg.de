<x-layouts.app>
    <x-slot:title>{{ $title ?? '' }}</x-slot>
    <x-slot:header>
        <div class="mx-auto max-w-5xl print:hidden text-sm flex items-center justify-between gap-x-6">
            <a
                href="{{ route('holocron.dashboard') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 !border-0 font-mono font-semibold"
            >
                <flux:icon.cpu-chip />
                <span> Holocron </span>
            </a>

            <div class="flex items-center gap-x-4">
                <a href="{{ route('holocron.experience') }}" wire:navigate>
                    <flux:badge as="button">{{ Number::format(number: auth()->user()->experience ?? 0, locale: 'de') }} XP: Level {{ auth()->user()->level }}</flux:badge>
                </a>
                <flux:button icon="cog" href="{{ route('holocron.settings') }}" variant="ghost" inset />
            </div>
        </div>
    </x-slot>

    <div class="mx-auto mt-6 max-w-5xl mb-24">
        {{ $slot }}

        <livewire:holocron.shared.bottom-navigation />
        <livewire:holocron.shared.command-modal.command-modal />
    </div>

    <x-slot:footer></x-slot>

    @persist('toast')
        <flux:toast />
    @endpersist

    
    <x-keyboard-shortcuts />

    <script type="module" src="{{ asset('js/longpress.js') }}"></script>
</x-layouts.app>
