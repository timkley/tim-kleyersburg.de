<div class="space-y-8">
    @include('holocron.gear.navigation')

    <form wire:submit.prevent="submit">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Gegenstand</flux:table.column>
                <flux:table.column>Kategorie</flux:table.column>
                <flux:table.column>Eigenschaften</flux:table.column>
                <flux:table.column>Anzahl</flux:table.column>
                <flux:table.column>Anzahl / Tag</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($items as $item)
                    <livewire:holocron.gear.items.components.item :$item :key="$item->id"/>
                @endforeach
                <flux:table.row>
                    <flux:table.cell>
                        <flux:input wire:model="name" name="name" placeholder="Artikelname"/>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:select wire:model="category_id" placeholder="Kategorie">
                            @foreach($categories as $id => $name)
                                <flux:select.option value="{{ $id }}">{{ $name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:select wire:model="properties" variant="listbox" multiple placeholder="Eigenschaften">
                            @foreach($availableProperties as $property)
                                <flux:select.option>{{ $property->value }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button variant="primary" type="submit">Erstellen</flux:button>
                    </flux:table.cell>
                </flux:table.row>
            </flux:table.rows>
        </flux:table>
    </form>
</div>
