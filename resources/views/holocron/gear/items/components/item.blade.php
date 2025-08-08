<flux:table.row>
    <flux:table.cell>
        <flux:input wire:model.live.debounce="name"></flux:input>
    </flux:table.cell>
    <flux:table.cell>
        <flux:select wire:model.live="category_id" placeholder="Kategorie">
            <flux:select.option disabled>---</flux:select.option>
            @foreach($categories as $id => $name)
                <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
            @endforeach
        </flux:select>
    </flux:table.cell>
    <flux:table.cell>
        <flux:select wire:model.live="properties" variant="listbox" multiple placeholder="Eigenschaften">
            @foreach($availableProperties as $property)
                <flux:select.option>{{ $property->value }}</flux:select.option>
            @endforeach
        </flux:select>
    </flux:table.cell>
    <flux:table.cell>
        <flux:input wire:model.live.debounce="quantity"></flux:input>
    </flux:table.cell>
    <flux:table.cell>
        <flux:input wire:model.live.debounce="quantity_per_day" :disabled="$quantity != 0"></flux:input>
    </flux:table.cell>
    <flux:table.cell>
        <flux:button wire:click="$parent.delete({{ $item->id }})" icon="trash" size="sm" variant="danger" ></flux:button>
    </flux:table.cell>
</flux:table.row>
