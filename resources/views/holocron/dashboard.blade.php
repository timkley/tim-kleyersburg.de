@use(App\Models\Holocron\Bookmark)

<x-slot:title>Holocron Dashboard</x-slot>

<div>
    <x-heading
        class="flex items-center gap-x-3"
        tag="h2"
    >
        Dashboard
        @if (auth()->user()->isTim())
            <a
                data-flux-button
                href="/holocron/pulse"
            >
                <flux:badge
                    color="sky"
                    icon="chart-bar"
                    >Pulse</flux:badge
                >
            </a>
        @endif
    </x-heading>

    <div class="grid sm:grid-cols-2 gap-4 md:grid-cols-3 md:gap-8">
        @if (auth()->user()->isTim())
            <a
                data-flux-button
                wire:navigate
                href="{{ route('holocron.water') }}"
            >
                <flux:card class="h-full hover:bg-white/75 dark:hover:bg-white/5">
                    <div>
                        <flux:heading
                            class="flex items-center gap-2 font-semibold"
                            size="lg"
                        >
                            <flux:icon.beaker />
                            Wassereinnahme
                        </flux:heading>
                        <flux:subheading> Heute {{ str_replace('.', ',', round($waterIntake / 1000, 1)) }}&nbsp;l getrunken </flux:subheading>
                        <flux:subheading> Es fehlen noch {{ str_replace('.', ',', round($remainingWater / 1000, 1)) }}&nbsp;l</flux:subheading>
                    </div>
                </flux:card>
            </a>

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
                        <flux:subheading> {{ Bookmark::count() }} Lesezeichen </flux:subheading>
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
