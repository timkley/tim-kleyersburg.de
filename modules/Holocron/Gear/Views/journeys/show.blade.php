<div class="space-y-8">
    @include('holocron-gear::navigation')

    <flux:heading size="xl">
        {{ $journey->destination }}
    </flux:heading>

    <p>
        {{ $journey->starts_at->format('d.m.Y') }} → {{ $journey->ends_at->format('d.m.Y') }}
    </p>

    <div class="grid grid-cols-7 gap-4">
        @foreach($journey->forecast()->days as $day)
            <div class="bg-white rounded p-3 text-sm">
                <p>
                    {{ $day->date->translatedFormat('D d.m.') }}
                </p>
                <p>
                    <img class="size-14" src="/img/weather_icons/{{ $day->wmoCode }}d_big.png" alt="{{ $day->condition }}">
                </p>
                <p>
                    {{ $day->maxTemp }}° / <span class="text-xs">{{ $day->minTemp }}°</span>
                </p>
            </div>
        @endforeach
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Anzahl</flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Hinfahrt</flux:table.column>
            <flux:table.column>Rückfahrt</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($groups as $group)
                <flux:table.row>
                    <flux:table.cell class="font-medium">{{ $group->first()->item->category->name }}</flux:table.cell>
                </flux:table.row>
                @foreach($group as $journeyItem)
                    <livewire:holocron.gear.journeys.components.journey-item :$journeyItem :key="$journeyItem->id" />
                @endforeach
            @endforeach
        </flux:table.rows>
    </flux:table>
    <flux:button variant="primary" wire:click="generatePacklist({{ $journey->id }})">Packliste generieren</flux:button>
</div>
