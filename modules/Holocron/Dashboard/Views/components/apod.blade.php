<flux:card class="h-full">
    <flux:heading
        class="flex items-center gap-2 font-semibold mb-4"
        size="lg"
    >
        <flux:icon.academic-cap/>
        Astronomy Picture of the Day
    </flux:heading>

    @if(!isset($apod))
        <flux:text>Laden...</flux:text>
    @else
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
    @endif
</flux:card>
