<x-layouts.app>
    <x-slot:title>{{ $title ?? '' }}</x-slot>
    <x-slot:header>
        <div class="mx-auto max-w-5xl">
            <a
                href="{{ route('holocron.dashboard') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 !border-0 font-mono text-sm font-semibold"
            >
                <flux:icon.cpu-chip />
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

    <x-slot:scripts>
        {{ $scripts ?? '' }}

        <script>
            document.addEventListener(
                'visibilitychange',
                () => {
                    if (!document.hidden) {
                        fetch('{{ route('holocron.helpers.csrf', absolute: false) }}')
                            .then((response) => response.json())
                            .then((data) => {
                                document.getElementById('livewire-script').dataset.csrf = data.csrf_token
                            })
                            .catch((error) => console.error('Error fetching CSRF token:', error))
                    }
                },
                false,
            )
        </script>
    </x-slot>
</x-layouts.app>
