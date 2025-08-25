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

        <flux:card class="h-full">
            <flux:heading
                class="flex items-center gap-2 font-semibold mb-4"
                size="lg"
            >
                <flux:icon.academic-cap/>
                Astronomy Picture of the Day
            </flux:heading>

            @php($apod = \App\Services\Nasa::apod())

            @if(!is_null($apod['url']))
                <flux:modal.trigger name="apod">
                    <img src="{{ $apod['url'] }}" alt="{{ $apod['title'] }}" class="rounded shadow-md mb-4"/>
                </flux:modal.trigger>
                <flux:modal name="apod" class="max-w-5xl">
                    <img src="{{ $apod['url'] }}" alt="{{ $apod['title'] }}" class="rounded shadow-md mb-4"/>
                </flux:modal>

                <flux:accordion>
                    <flux:accordion.item>
                        <flux:accordion.heading>{{ $apod['title'] }}</flux:accordion.heading>
                        <flux:accordion.content>
                            <p>{{ $apod['explanation'] }}</p>
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
            @else
                <flux:text>Bildabruf fehlgeschlagen.</flux:text>
            @endif
        </flux:card>
    </div>
</div>
