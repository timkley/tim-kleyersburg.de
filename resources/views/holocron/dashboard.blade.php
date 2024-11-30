<x-slot:title>Holocron Dashboard</x-slot>

<div class="grid grid-cols-2 gap-8 md:grid-cols-4">
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

    @if (auth()->user()->isTim())
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
</div>
