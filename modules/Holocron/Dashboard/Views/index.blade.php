<div>
    <x-heading
        class="flex items-center gap-x-3"
        tag="h2"
    >
        Dashboard
        @if (auth()->user()->isTim())
            <flux:button
                variant="filled"
                href="/holocron/pulse"
                icon="chart-bar"
                size="sm"
            >
                Pulse
            </flux:button>
        @endif
    </x-heading>

    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 md:gap-8">
        @if (auth()->user()->isTim())
            <livewire:holocron.dashboard.components.goals/>
        @endif

        <flux:card>
            <flux:heading
                class="flex items-center justify-between font-semibold"
                size="lg"
            >
                <div class="flex items-center gap-2">
                    <flux:icon.link/>
                    Quests
                </div>
                <flux:button
                    variant="filled"
                    href="{{ route('holocron.quests.daily') }}"
                    icon="calendar"
                    size="sm"
                >
                    Daily
                </flux:button>
            </flux:heading>
            <flux:subheading class="space-y-2">
                @foreach($accepted_quests as $quest)
                    <livewire:holocron.quest.components.item :$quest :key="$quest->id" :show-parent="false"/>
                @endforeach
            </flux:subheading>
        </flux:card>

        <flux:card class="space-y-4">
            <div>
                <flux:heading
                    class="flex items-center gap-2 font-semibold"
                    size="lg"
                >
                    <flux:icon.link/>
                    Lesezeichen
                </flux:heading>
                <flux:subheading>
                    <a href="{{ route('holocron.bookmarks') }}" wire:navigate>
                        {{ \Modules\Holocron\Bookmarks\Models\Bookmark::count() }} Lesezeichen
                    </a>
                </flux:subheading>
            </div>

            <div>
                <flux:heading
                    class="flex items-center gap-2 font-semibold"
                    size="lg"
                >
                    <flux:icon.academic-cap/>
                    Schule
                </flux:heading>

                <flux:subheading class="space-y-2">
                    <p>
                        <a
                            href="{{ route('holocron.school.information') }}"
                            wire:navigate
                        >
                            Informationen
                        </a>
                    </p>
                    <p>
                        <a
                            href="{{ route('holocron.school.vocabulary.overview') }}"
                            wire:navigate
                        >
                            Vokabeln
                        </a>
                    </p>
                </flux:subheading>
            </div>
        </flux:card>

        <livewire:holocron.dashboard.components.apod />
    </div>
</div>
