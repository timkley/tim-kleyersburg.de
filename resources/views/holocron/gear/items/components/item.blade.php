<flux:table.row>
    <flux:table.cell>
        <flux:input wire:model.live.debounce="name"></flux:input>
        @foreach($item->properties ?? [] as $property)
            {{ $property }}
        @endforeach
    </flux:table.cell>
    <flux:table.cell>
        <flux:button variant="danger" wire:click="$parent.delete({{ $item->id }})">Löschen</flux:button>
    </flux:table.cell>
</flux:table.row>
