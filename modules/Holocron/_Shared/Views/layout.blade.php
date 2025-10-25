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

            <livewire:holocron.shared.printer-status />
        </div>
    </x-slot>

    <div class="mx-auto mt-6 max-w-5xl mb-24">
        {{ $slot }}

        <livewire:holocron.shared.bottom-navigation />
        <livewire:holocron.shared.command-modal />
        <livewire:holocron.shared.search-modal />
    </div>

    <x-slot:footer></x-slot>

    @persist('toast')
        <flux:toast />
        <x-keyboard-shortcuts />
    @endpersist

    <script type="module" src="{{ asset('js/longpress.js') }}"></script>
</x-layouts.app>
