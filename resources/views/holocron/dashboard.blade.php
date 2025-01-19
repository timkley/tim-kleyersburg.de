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
                    >Pulse
                </flux:badge>
            </a>
        @endif
    </x-heading>

    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 md:gap-8">
        @if (auth()->user()->isTim())
            <flux:card class="sm:col-span-2 md:col-span-3">
                <flux:heading
                    class="flex items-center gap-2 font-semibold"
                    size="lg"
                >
                    <flux:icon.shield-check />
                    Ziele
                </flux:heading>
                <div class="mt-2 gap-4 sm:grid sm:grid-cols-3 md:gap-8">
                    @foreach ($dailyGoals as $goal)
                        <x-dynamic-component
                            :component="'holocron.dashboard.goals.'.$goal->type->value"
                            :goal="$goal"
                        />
                    @endforeach
                </div>
            </flux:card>
            <livewire:holocron.dashboard.bookmarks />
        @endif

        <flux:card class="h-full hover:bg-white/75 dark:hover:bg-white/5">
            <div>
                <flux:heading
                    class="flex items-center gap-2 font-semibold"
                    size="lg"
                >
                    <flux:icon.academic-cap />
                    Schule
                </flux:heading>

                <flux:subheading class="space-y-2">
                    <p>
                        <a
                            href="{{ route('holocron.school.information') }}"
                            wire:navigate
                            >Informationen</a
                        >
                    </p>
                    <p>
                        <a
                            href="{{ route('holocron.school.vocabulary.overview') }}"
                            wire:navigate
                            >Vokabeln</a
                        >
                    </p>
                </flux:subheading>
            </div>
        </flux:card>
    </div>
</div>
