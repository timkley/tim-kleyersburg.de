<div class="space-y-8">
    @include('holocron-gear::navigation')

    <div>
        <form class="space-y-2" wire:submit="submit">
            <div class="flex flex-col sm:flex-row items-center gap-2">
                <flux:input icon="map-pin" wire:model.live="destination" placeholder="Zielort"/>
                <flux:input type="date" wire:model.live="starts_at" placeholder="Ankunft"/>
                <flux:input type="date" wire:model.live="ends_at" placeholder="Abfahrt"/>

                @if(\Modules\Holocron\Gear\Enums\Property::ChildOnBoard->isJourneyApplicable())
                    <flux:button 
                        wire:click="toggleProperty('child-on-board')"
                        variant="{{ in_array('child-on-board', array_column($selectedProperties, 'value')) ? 'primary' : 'filled' }}"
                        class="w-full"
                    >
                        Mit Kind
                    </flux:button>
                @endif
            </div>

            <flux:button variant="primary" type="submit">Reise anlegen</flux:button>
        </form>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Ziel</flux:table.column>
            <flux:table.column>Anfahrt</flux:table.column>
            <flux:table.column>Abfahrt</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($journeys as $journey)
                <flux:table.row>
                    <flux:table.cell>
                        <a href="{{ route('holocron.gear.journeys.show', $journey->id) }}" wire:navigate>
                            {{ $journey->destination }}
                        </a>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $journey->starts_at->format('d.m.Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $journey->ends_at->format('d.m.Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <a href="{{ route('holocron.gear.journeys.show', $journey->id) }}" wire:navigate>
                            <flux:button>Ansehen</flux:button>
                        </a>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
