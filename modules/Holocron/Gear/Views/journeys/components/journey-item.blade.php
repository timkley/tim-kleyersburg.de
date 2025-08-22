<flux:table.row>
    <flux:table.cell>
        {{ $journeyItem->quantity }} x
    </flux:table.cell>
    <flux:table.cell>
        {{ $journeyItem->item->name }}
    </flux:table.cell>
    <flux:table.cell>
        <flux:switch wire:model.live="packed_for_departure" />
    </flux:table.cell>
    <flux:table.cell>
        <flux:switch wire:model.live="packed_for_return" />
    </flux:table.cell>
    <flux:table.cell>
        <flux:button wire:click="delete" icon="trash" size="sm" variant="danger"></flux:button>
    </flux:table.cell>
</flux:table.row>
