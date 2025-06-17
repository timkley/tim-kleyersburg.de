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

            <a href="{{ route('holocron.experience') }}" wire:navigate>
                <flux:badge as="button">{{ Number::format(number: auth()->user()->experience ?? 0, locale: 'de') }} XP: Level {{ auth()->user()->level }}</flux:badge>
            </a>
        </div>
    </x-slot>

    <div class="mx-auto mt-6 max-w-5xl mb-24">
        {{ $slot }}

        <livewire:holocron.bottom-navigation />
    </div>

    <x-slot:footer></x-slot>

    @persist('toast')
        <flux:toast />
    @endpersist
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('longpress', (config = {}) => ({
                timer: null,
                isPressed: false,
                delay: config.delay || 500,
                onLongPress: config.onLongPress || (() => {}),

                handleStart() {
                    this.isPressed = true;
                    this.timer = setTimeout(() => {
                        this.onLongPress();
                        this.isPressed = false;
                    }, this.delay);
                },

                handleEnd() {
                    this.isPressed = false;
                    clearTimeout(this.timer);
                },

                get events() {
                    return {
                        '@mousedown'() { this.handleStart(); },
                        '@mouseup'() { this.handleEnd(); },
                        '@mouseleave'() { this.handleEnd(); },
                        '@touchstart'() { this.handleStart(); },
                        '@touchend'() { this.handleEnd(); },
                        '@touchcancel'() { this.handleEnd(); },
                        '@contextmenu.prevent'() {},
                        '@selectstart.prevent'() {},
                        '@dragstart.prevent'() {}
                    };
                }
            }));
        })
    </script>
</x-layouts.app>
