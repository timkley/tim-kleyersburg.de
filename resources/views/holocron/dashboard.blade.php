<x-slot:title>Holocron Dashboard</x-slot>

<div>
    <x-heading tag="h2">Dashboard</x-heading>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-8">
        @if (auth()->user()->isTim())
            <a
                data-flux-button
                wire:navigate
                href="{{ route('holocron.bookmarks') }}"
            >
                <flux:card class="h-full hover:bg-white/75 dark:hover:bg-white/5">
                    <div>
                        <flux:heading
                            class="flex items-center gap-2 font-semibold"
                            size="lg"
                        >
                            <flux:icon.link />
                            Lesezeichen
                        </flux:heading>
                    </div>
                </flux:card>
            </a>

            <a
                data-flux-button
                href="/holocron/pulse"
            >
                <flux:card class="h-full hover:bg-white/75 dark:hover:bg-white/5">
                    <div>
                        <flux:heading
                            class="flex items-center gap-2 font-semibold"
                            size="lg"
                        >
                            <flux:icon.chart-bar />
                            Pulse
                        </flux:heading>
                    </div>
                </flux:card>
            </a>
        @endif

        <flux:card class="h-full hover:bg-white/75 dark:hover:bg-white/5">
            <div>
                <flux:heading
                    class="flex items-center gap-2 font-semibold"
                    size="lg"
                >
                    <flux:icon.academic-cap />
                    Schule Emi
                </flux:heading>

                <flux:subheading class="space-y-2">
                    <p>
                        <a href="{{ route('holocron.school.information') }}">Informationen</a>
                    </p>
                    <p>
                        <a href="{{ route('holocron.school.vocabulary.overview') }}">Vokabeln</a>
                    </p>
                </flux:subheading>
            </div>
        </flux:card>
    </div>
</div>
