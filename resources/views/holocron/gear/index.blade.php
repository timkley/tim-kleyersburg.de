<div class="space-y-8">
    @include('holocron.gear.navigation')

    <div>
        <flux:heading>Reise starten</flux:heading>

        <form wire:submit="submit">
            <div class="flex items-center gap-x-4">
                <flux:input icon="map-pin" wire:model.live="destination" placeholder="Zielort" />
                <flux:input type="date" wire:model.live="starts_at" placeholder="Ankunft" />
                <flux:input type="date" wire:model.live="ends_at" placeholder="Abfahrt" />
                @if($this->conditionIcon)
                    <img src="{{ $this->conditionIcon }}" alt="">
                @endif
                {{ $this->days }} Tage
            </div>

            <flux:button variant="primary" type="submit">Reise anlegen</flux:button>
        </form>
    </div>

    <div>
        @foreach($journeys as $journey)
            <a href="{{ route('holocron.gear.journeys.show', $journey->id) }}" wire:navigate>
                {{ $journey->destination }}
            </a>
            {{ $journey->starts_at }}
            {{ $journey->ends_at }}
        @endforeach
    </div>
</div>
